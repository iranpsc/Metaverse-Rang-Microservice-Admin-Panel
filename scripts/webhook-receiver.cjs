/* Temporary loopback-only receiver for debugging the admin webhook contract.
 * Run: node scripts/webhook-receiver.cjs
 * Test secret only; never configure this receiver as a production destination.
 */
const http = require('node:http');
const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');

const port = Number(process.env.WEBHOOK_TEST_PORT || 4900);
const secret = process.env.WEBHOOK_TEST_SECRET || 'local-webhook-test-secret';
const logFile = path.resolve(process.env.WEBHOOK_TEST_LOG || 'storage/logs/webhook-receiver.ndjson');
const accepted = new Map();
const requests = [];
let mode = 'success';

const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://127.0.0.1');
  const reply = (status, data) => {
    if (res.destroyed) return;
    res.writeHead(status, { 'Content-Type': 'application/json', 'Cache-Control': 'no-store' });
    res.end(JSON.stringify(data));
  };
  if (req.method === 'GET' && url.pathname === '/health') return reply(200, { status: 'ready', mode });
  if (req.method === 'GET' && url.pathname === '/__test/requests') return reply(200, { requests, accepted: [...accepted.keys()] });
  if (req.method === 'GET' && url.pathname === '/') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end('<h1>Temporary webhook receiver</h1><p>Loopback only. Test traffic only.</p><p><a href="/__test/requests">Inspect captured requests</a> · <a href="/health">Health</a></p><p>POST /api/webhooks/cache-revalidate</p>');
  }
  const chunks = [];
  let size = 0;
  for await (const chunk of req) {
    size += chunk.length;
    if (size > 8 * 1024 * 1024) return reply(413, { error: 'Test receiver limit: 8MB' });
    chunks.push(chunk);
  }
  const bytes = Buffer.concat(chunks);
  const raw = bytes.toString('utf8');
  if (req.method === 'POST' && url.pathname === '/__test/reset') {
    requests.length = 0; accepted.clear(); mode = 'success';
    return reply(200, { status: 'reset' });
  }
  if (req.method === 'POST' && url.pathname === '/__test/mode') {
    try { mode = JSON.parse(raw).mode || 'success'; return reply(200, { mode }); }
    catch { return reply(400, { error: 'Invalid JSON' }); }
  }
  if (req.method !== 'POST' || !['/api/webhooks/cache-revalidate', '/nextjs/api/webhooks/cache-revalidate'].includes(url.pathname)) return reply(404, { error: 'Unknown endpoint' });
  const entry = { received_at: new Date().toISOString(), raw, mode: url.searchParams.get('failure') || mode };
  requests.push(entry);
  const finish = (status, data) => {
    entry.status = status;
    fs.mkdirSync(path.dirname(logFile), { recursive: true });
    fs.appendFileSync(logFile, JSON.stringify(entry) + '\n');
    reply(status, data);
  };
  const supplied = Buffer.from(req.headers['x-webhook-secret'] || '');
  const expected = Buffer.from(secret);
  if (supplied.length !== expected.length || !crypto.timingSafeEqual(supplied, expected)) return finish(401, { error: 'Invalid webhook secret' });
  let body;
  try { body = JSON.parse(raw); } catch { return finish(400, { error: 'Invalid JSON' }); }
  entry.body = body;
  entry.batch_id = body.batch_id;
  entry.version = body.version;
  const routes = body.affected_routes;
  if (body.event !== 'admin.content.batch' || !/^[0-9a-f-]{36}$/i.test(body.batch_id || '') || !/^V1\.1\.[1-9][0-9]*$/.test(body.version || '') || !Array.isArray(body.changes) || !body.changes.length || !Array.isArray(routes) || !routes.length || new Set(routes).size !== routes.length) return finish(422, { error: 'Invalid batch contract' });
  const union = new Set();
  for (const change of body.changes) {
    if (!change.feature || !change.entity_id || !change.resource || !Array.isArray(change.actions) || !change.actions.every(a => ['CREATE', 'UPDATE', 'DELETE'].includes(a)) || !Array.isArray(change.modifiers) || !Array.isArray(change.affected_routes)) return finish(422, { error: 'Invalid change contract' });
    change.affected_routes.forEach(route => union.add(route));
  }
  if (union.size !== routes.length || routes.some(route => !union.has(route))) return finish(422, { error: 'Route union mismatch' });
  if (accepted.has(body.batch_id) && accepted.get(body.batch_id) !== raw) return finish(409, { error: 'Batch ID reused with changed payload' });
  const failure = entry.mode;
  if (/^[45]\d\d$/.test(failure)) return finish(Number(failure), { error: 'Injected HTTP failure' });
  if (failure === 'disconnect') { entry.status = 'disconnected'; req.socket.destroy(); return; }
  if (failure === 'bad-ack') return finish(200, { batch_id: 'wrong', status: 'accepted' });
  if (failure === 'html') { entry.status = 502; res.writeHead(502, { 'Content-Type': 'text/html' }); return res.end('<h1>Bad gateway</h1>'); }
  if (failure === 'timeout') return setTimeout(() => finish(504, { error: 'Injected timeout' }), 20000);
  entry.duplicate = accepted.has(body.batch_id);
  accepted.set(body.batch_id, raw);
  if (failure === 'accepted-timeout') return setTimeout(() => finish(200, { batch_id: body.batch_id, status: 'accepted' }), 20000);
  if (failure === 'slow') return setTimeout(() => finish(200, { batch_id: body.batch_id, status: 'accepted' }), 2000);
  finish(200, { batch_id: body.batch_id, status: 'accepted' });
});
server.listen(port, '127.0.0.1', () => console.log(JSON.stringify({ event: 'listening', url: `http://127.0.0.1:${port}`, logFile })));
for (const signal of ['SIGINT', 'SIGTERM']) process.on(signal, () => { server.closeAllConnections(); server.close(() => process.exit(0)); });
