export default [
  { id: 'dashboard', label: 'داشبورد', route: '/', icon: 'home', color: 'primary', roles: [], permissions: ['view-dashboard'] },
  {
    id: 'citizens',
    label: 'شهروندان',
    route: '#',
    icon: 'user',
    color: 'secondary',
    roles: ['citizens-management'],
    permissions: [],
    children: [
      { id: 'citizens-registration', label: 'مشخصات ثبت نام', route: '/citizens/registration-info', icon: 'user', color: 'secondary', permissions: ['view-registration-info'] },
      { id: 'citizens-kycs', label: 'احراز هویت', route: '/citizens/kycs', icon: 'user', color: 'secondary', permissions: ['verify-kyc'] },
      { id: 'citizens-bank-accounts', label: 'حساب های بانکی', route: '/citizens/bank-accounts', icon: 'user', color: 'secondary', permissions: ['verify-bank-accounts'] },
      { id: 'citizens-deposits', label: 'واریزی ها', route: '/citizens/deposits', icon: 'user', color: 'secondary', permissions: ['view-deposits'] },
      { id: 'citizens-withdraws', label: 'برداشت ها', route: '/citizens/withdraws', icon: 'user', color: 'secondary', permissions: ['view-withdraws'] },
      { id: 'citizens-profile-details', label: 'جزئیات پروفایل', route: '/citizens/profile-details', icon: 'user', color: 'secondary', permissions: ['view-profile-details'] },
      { id: 'citizens-assets', label: 'دارایی ها', route: '/citizens/assets', icon: 'user', color: 'secondary', permissions: ['view-assets'] }
    ]
  },
  {
    id: 'features', label: 'زمین ها', route: '#', icon: 'cube', color: 'primary', roles: [], permissions: [],
    children: [
      { id: 'features-all', label: 'کل زمین ها', route: '/features/all', icon: 'cube', color: 'primary', permissions: ['manage-features-info'] },
      { id: 'features-limits', label: 'محدودیت های املاک', route: '/features/limits', icon: 'cube', color: 'primary', permissions: ['manage-features-info'] },
      { id: 'features-pricing-limits', label: 'محدودیت های قیمت گذاری', route: '/features/pricing-limits', icon: 'cube', color: 'primary', permissions: ['manage-pricing-limits'] },
      { id: 'features-prices', label: 'قیمت زمین ها', route: '/features/prices', icon: 'cube', color: 'primary', permissions: ['view-features-prices'] },
      { id: 'features-priced', label: 'قیمت گذاری زمین', route: '/features/priced', icon: 'cube', color: 'primary', permissions: ['view-priced-features'] },
      { id: 'features-sold', label: 'زمین های فروخته شده', route: '/features/sold', icon: 'cube', color: 'primary', permissions: ['view-sold-features'] },
      { id: 'features-trades', label: 'مبادله زمین', route: '/features/trades', icon: 'cube', color: 'primary', permissions: ['view-features-trades'] }
    ]
  },
  {
    id: 'access-management', label: 'مدیریت دسترسی ها', route: '#', icon: 'key', color: 'primary', roles: ['access-management'], permissions: ['manage-access'],
    children: [
      { id: 'access-employees', label: 'مدیران', route: '/access-management/employees', icon: 'user', color: 'primary', permissions: [] },
      { id: 'access-roles', label: 'مسئولیت ها', route: '/access-management/roles', icon: 'key', color: 'primary', permissions: [] },
      { id: 'access-permissions', label: 'دسترسی ها', route: '/access-management/permissions', icon: 'key', color: 'primary', permissions: [] }
    ]
  },
  {
    id: 'support', label: 'پشتیبانی', route: '#', icon: 'phone', color: 'rose', roles: ['support-management'], permissions: [],
    children: [
      { id: 'support-citizens-safety', label: 'امنیت شهروندان', route: '/support/citizens_safety', icon: 'phone', color: 'rose', permissions: ['respond-to-citziens-safety-tickets'] },
      { id: 'support-technical-support', label: 'پشتیبانی فنی', route: '/support/technical_support', icon: 'phone', color: 'rose', permissions: ['respond-to-technical-support-tickets'] },
      { id: 'support-investment', label: 'سرمایه گذاری', route: '/support/investment', icon: 'phone', color: 'rose', permissions: ['respond-to-investment-tickets'] },
      { id: 'support-inspection', label: 'بازرسی', route: '/support/inspection', icon: 'phone', color: 'rose', permissions: ['respond-to-inspection-tickets'] },
      { id: 'support-protection', label: 'حراست', route: '/support/protection', icon: 'phone', color: 'rose', permissions: ['respond-to-protection-tickets'] },
      { id: 'support-ztb-management', label: 'مدیریت کل ز.ت.ب', route: '/support/ztb', icon: 'phone', color: 'rose', permissions: ['respond-to-ztb-management-tickets'] }
    ]
  },
  {
    id: 'store', label: 'فروشگاه', route: '#', icon: 'shoppingCart', color: 'emerald', roles: [], permissions: [],
    children: [
      { id: 'store-packages', label: 'بسته ها', route: '/store/packages', icon: 'shoppingCart', color: 'emerald', permissions: ['manage-packages'] },
      { id: 'store-currencies', label: 'ارزها', route: '/store/currencies', icon: 'shoppingCart', color: 'emerald', permissions: ['manage-currencies'] }
    ]
  },
  {
    id: 'dynasty', label: 'سلسله', route: '#', icon: 'users', color: 'yellow', roles: [], permissions: [],
    children: [
      { id: 'dynasty-prizes', label: 'جوایزه سلسله', route: '/dynasty/prizes', icon: 'users', color: 'yellow', permissions: ['manage-dynasty-prizes'] },
      { id: 'dynasty-messages', label: 'پیام های سلسله', route: '/dynasty/messages', icon: 'users', color: 'yellow', permissions: ['manage-dynasty-messages'] },
      { id: 'dynasty-permissions', label: 'دسترسی ها', route: '/dynasty/permissions', icon: 'users', color: 'yellow', permissions: ['manage-dynasty-permissions'] }
    ]
  },
  { id: 'map-management', label: 'مدیریت نقشه ها', route: '/maps', icon: 'map', color: 'primary', roles: [], permissions: ['manage-maps'] },
  {
    id: 'levels-group',
    label: 'مدیریت سطوح',
    route: '#',
    icon: 'levelUp',
    color: 'primary',
    roles: ['level-management'],
    permissions: ['manage-level'],
    children: [
      { id: 'levels', label: 'مدیریت سطوح', route: '/levels', icon: 'levelUp', color: 'primary', permissions: [] },
      { id: 'user-levels', label: 'سطوح کاربران', route: '/user-levels', icon: 'user', color: 'primary', permissions: [] }
    ]
  },
  { id: 'calendar', label: 'تقویم', route: '/calendar', icon: 'calendar', color: 'primary', roles: ['calendar-management'], permissions: ['manage-calendar'] },
  { id: 'versions', label: 'ورژن ها', route: '/versions', icon: 'list', color: 'primary', roles: ['versions-management'], permissions: ['manage-versions'] },
  { id: 'reports', label: 'گزارشات کاربران', route: '/reports', icon: 'eye', color: 'blue', roles: ['reports-management'], permissions: ['manage-repots'] },
  { id: 'system-variables', label: 'متغیرهای سیستم', route: '/system-variables', icon: 'puzzle', color: 'primary', roles: [], permissions: ['manage-system-variables'] },
  {
    id: 'challenge', label: 'چالش پرسش و پاسخ', route: '#', icon: 'question', color: 'yellow',     roles: ['level-management'], permissions: ['manage-level', 'manage-challenge'],
    children: [{ id: 'challenge-list', label: 'لیست سوالات', route: '/challenge', icon: 'list', color: 'yellow', permissions: [] }]
  },
  {
    id: 'tutorials', label: 'فیلم های آموزشی', route: '#', icon: 'video', color: 'blue', roles: ['tutorials-management'], permissions: ['manage-tutorials'],
    children: [
      { id: 'tutorials-videos', label: 'ویدیوها', route: '/videos/listing', icon: 'video', color: 'blue', permissions: [] },
      { id: 'tutorials-categories', label: 'دسته بندی ویدیوها', route: '/videos/categories', icon: 'list', color: 'blue', permissions: [] },
      { id: 'tutorials-sub-categories', label: 'زیر دسته های ویدیو', route: '/videos/sub-categories', icon: 'list', color: 'blue', permissions: [] }
    ]
  },
  { id: 'translations', label: 'ترجمه', route: '/translations', icon: 'list', color: 'primary', roles: ['access-management'], permissions: ['manage-access', 'manage-translations'] },
  { id: 'isic-codes', label: 'کدهای ISIC', route: '/isic-codes', icon: 'list', color: 'primary', roles: ['access-management'], permissions: ['manage-access', 'manage-isic-codes'] },
  { id: 'activity-logs', label: 'گزارش فعالیت‌ها', route: '/activity-logs', icon: 'list', color: 'blue', roles: [], permissions: ['view-activity-logs'] }
]
