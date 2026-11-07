// Local receiver derived from docs/content-webhooks.openapi.json, not a live Next.js implementation.
const http = require('http');
const crypto = require('crypto');
const accepted = new Map();
const secret = 'local-webhook-test-secret';
http.createServer((req, res) => {
  const reply = (status, data) => { res.writeHead(status, { 'Content-Type': 'application/json' }); res.end(JSON.stringify(data)); };
  if (req.method !== 'POST' || !req.url.startsWith('/api/webhooks/cache-revalidate')) return reply(501, { error: 'Not modeled' });
  const finish = raw => {
    try {
      const payload = JSON.parse(raw);
      const supplied = Buffer.from(req.headers['x-webhook-secret'] || '');
      const expected = Buffer.from(secret);
      if (supplied.length !== expected.length || !crypto.timingSafeEqual(supplied, expected)) return reply(401, { error: 'Invalid webhook secret' });
      if (payload.event !== 'admin.content.batch' || !payload.batch_id || !payload.version || !Array.isArray(payload.affected_routes)) return reply(422, { error: 'Invalid contract' });
      if (req.url.includes('failure=503')) return reply(503, { error: 'Temporarily unavailable' });
      if (req.url.includes('failure=401')) return reply(401, { error: 'Rejected' });
      if (req.url.includes('failure=timeout')) return setTimeout(() => reply(504, { error: 'Delayed' }), 20000);
      const prior = accepted.get(payload.batch_id);
      if (prior && prior !== raw) return reply(409, { error: 'Batch ID reused with another body' });
      accepted.set(payload.batch_id, raw);
      reply(200, { batch_id: payload.batch_id, status: 'accepted' });
    } catch { reply(400, { error: 'Invalid JSON' }); }
  };
  let raw = '';
  req.on('data', chunk => { raw += chunk; });
  req.on('end', () => finish(raw));
}).listen(process.env.PORT || 4901);
