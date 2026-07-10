import { createRouter, createWebHistory } from 'vue-router'
import Login from '../pages/auth/Login.vue'
import ForgotPassword from '../pages/auth/ForgotPassword.vue'
import ResetPassword from '../pages/auth/ResetPassword.vue'
import App from '../components/App.vue'
import NotFound from '../components/errors/NotFound.vue'
import Forbidden from '../components/errors/Forbidden.vue'
import { navigationProgress } from '../composables/useNavigationProgress'
import { useAuth } from '../composables/useAuth'
import { canAccess, clearAuthStorage, hasValidAuthToken } from '../utils/authorization'
import { resolveRouteAccess } from './routeAccess'
const Dashboard = () => import('../pages/Dashboard.vue')
const RegistrationInfo = () => import('../pages/citizens/RegistrationInfo.vue')
const KycList = () => import('../pages/citizens/KycList.vue')
const BankAccountList = () => import('../pages/citizens/BankAccountList.vue')
const Assets = () => import('../pages/citizens/Assets.vue')
const Deposits = () => import('../pages/citizens/Deposits.vue')
const ProfileDetails = () => import('../pages/citizens/ProfileDetails.vue')
const Withdraws = () => import('../pages/citizens/Withdraws.vue')
const LandsListing = () => import('../pages/lands/Listing.vue')
const FeatureLimits = () => import('../pages/lands/FeatureLimits.vue')
const FeaturePricingLimits = () => import('../pages/lands/FeaturePricingLimits.vue')
const Prices = () => import('../pages/lands/Prices.vue')
const Pricing = () => import('../pages/lands/Pricing.vue')
const Sold = () => import('../pages/lands/Sold.vue')
const Traded = () => import('../pages/lands/Traded.vue')
const LevelsListing = () => import('../pages/levels/Listing.vue')
const UserLevelsListing = () => import('../pages/levels/UserLevels.vue')
const CalendarListing = () => import('../pages/calendar/Listing.vue')
const Versions = () => import('../pages/calendar/Versions.vue')
const LevelPrize = () => import('../pages/levels/Prize.vue')
const LevelLicenses = () => import('../pages/levels/Licenses.vue')
const LevelGift = () => import('../pages/levels/Gift.vue')
const LevelGeneralInfo = () => import('../pages/levels/GeneralInfo.vue')
const LevelGem = () => import('../pages/levels/Gem.vue')
const Roles = () => import('../pages/access-management/Roles.vue')
const Permissions = () => import('../pages/access-management/Permissions.vue')
const EmployeeRolePermission = () => import('../pages/access-management/EmployeeRolePermission.vue')
const SupportTickets = () => import('../pages/support/SupportTickets.vue')
const ColorsPrice = () => import('../pages/variables/ColorsPrice.vue')
const ColorOptions = () => import('../pages/variables/ColorOptions.vue')
const SystemVariables = () => import('../pages/variables/SystemVariables.vue')
const ChallengeQuestions = () => import('../pages/challenge/Questions.vue')
const VideoCategories = () => import('../pages/videos/Categories.vue')
const VideoSubCategories = () => import('../pages/videos/SubCategories.vue')
const VideoListing = () => import('../pages/videos/Listing.vue')
const DynastyMessages = () => import('../pages/dynasty/DynastyMessages.vue')
const DynastyPermissions = () => import('../pages/dynasty/DynastyPermissions.vue')
const DynastyPrizes = () => import('../pages/dynasty/DynastyPrizes.vue')
const MapsListing = () => import('../pages/maps/MapsListing.vue')
const ReportsListing = () => import('../pages/reports/Listing.vue')
const TranslationsIndex = () => import('../pages/translations/TranslationsIndex.vue')
const TranslationModals = () => import('../pages/translations/TranslationModals.vue')
const ModalTabs = () => import('../pages/translations/ModalTabs.vue')
const TabFields = () => import('../pages/translations/TabFields.vue')
const IsicCodes = () => import('../pages/isic-codes/Listing.vue')
const ActivityLogs = () => import('../pages/activity-logs/Listing.vue')
const Profile = () => import('../pages/profile/Profile.vue')

const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: {
      requiresGuest: true,
      layout: 'auth',
      title: 'ورود به سیستم'
    }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: {
      requiresGuest: true,
      layout: 'auth',
      title: 'بازیابی رمز عبور'
    }
  },
  {
    path: '/reset-password/:token',
    name: 'reset-password',
    component: ResetPassword,
    meta: {
      requiresGuest: true,
      layout: 'auth',
      title: 'بازیابی رمز عبور'
    },
    props: true
  },
  {
    path: '/',
    component: App,
    meta: {
      requiresAuth: true
    },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: Dashboard,
        meta: {
          title: 'داشبورد'
        }
      },
      {
        path: 'profile',
        name: 'profile',
        component: Profile,
        meta: {
          title: 'پروفایل من'
        }
      },
      {
        path: 'forbidden',
        name: 'forbidden',
        component: Forbidden,
        meta: {
          title: 'دسترسی غیرمجاز'
        }
      },
      {
        path: 'videos/categories',
        name: 'video-categories',
        component: VideoCategories,
        meta: {
          title: 'دسته بندی ویدیوها'
        }
      },
      {
        path: 'videos/sub-categories',
        name: 'video-sub-categories',
        component: VideoSubCategories,
        meta: {
          title: 'زیر دسته های ویدیو'
        }
      },
      {
        path: 'videos/listing',
        name: 'videos-listing',
        component: VideoListing,
        meta: {
          title: 'مدیریت ویدیوها'
        }
      },
      {
        path: 'citizens/registration-info',
        name: 'registration-info',
        component: RegistrationInfo,
        meta: {
          title: 'اطلاعات ثبت نام'
        }
      },
      {
        path: 'citizens/kycs',
        name: 'kycs',
        component: KycList,
        meta: {
          title: 'احراز هویت'
        }
      },
      {
        path: 'citizens/bank-accounts',
        name: 'bank-accounts',
        component: BankAccountList,
        meta: {
          title: 'حساب های بانکی'
        }
      },
      {
        path: 'citizens/assets',
        name: 'assets',
        component: Assets,
        meta: {
          title: 'دارایی های شهروندان'
        }
      },
      {
        path: 'citizens/deposits',
        name: 'deposits',
        component: Deposits,
        meta: {
          title: 'واریزی ها'
        }
      },
      {
        path: 'citizens/profile-details',
        name: 'profile-details',
        component: ProfileDetails,
        meta: {
          title: 'جزئیات پروفایل'
        }
      },
      {
        path: 'citizens/withdraws',
        name: 'withdraws',
        component: Withdraws,
        meta: {
          title: 'برداشت ها'
        }
      },
      {
        path: 'features/all',
        name: 'lands-listing',
        component: LandsListing,
        meta: {
          title: 'لیست املاک'
        }
      },
      {
        path: 'features/limits',
        name: 'feature-limits',
        component: FeatureLimits,
        meta: {
          title: 'محدودیت املاک'
        }
      },
      {
        path: 'features/pricing-limits',
        name: 'feature-pricing-limits',
        component: FeaturePricingLimits,
        meta: {
          title: 'محدودیت‌های قیمت'
        }
      },
      {
        path: 'features/prices',
        name: 'lands-prices',
        component: Prices,
        meta: {
          title: 'قیمت زمین ها'
        }
      },
      {
        path: 'features/priced',
        name: 'lands-pricing',
        component: Pricing,
        meta: {
          title: 'لیست قیمت گذاری ها'
        }
      },
      {
        path: 'features/sold',
        name: 'lands-sold',
        component: Sold,
        meta: {
          title: 'لیست زمین های فروخته شده'
        }
      },
      {
        path: 'features/trades',
        name: 'lands-traded',
        component: Traded,
        meta: {
          title: 'لیست زمین های معامله شده'
        }
      },
      {
        path: 'levels',
        name: 'levels-listing',
        component: LevelsListing,
        meta: {
          title: 'مدیریت سطوح'
        }
      },
      {
        path: 'user-levels',
        name: 'user-levels',
        component: UserLevelsListing,
        meta: {
          title: 'سطوح کاربران'
        }
      },
      {
        path: 'calendar',
        name: 'calendar-listing',
        component: CalendarListing,
        meta: {
          title: 'مدیریت وقایع'
        }
      },
      {
        path: 'versions',
        name: 'versions',
        component: Versions,
        meta: {
          title: 'ورژن‌ها'
        }
      },
      {
        path: 'reports',
        name: 'reports',
        component: ReportsListing,
        meta: {
          title: 'گزارشات کاربران'
        }
      },
      {
        path: 'system-variables',
        name: 'system-variables',
        component: SystemVariables,
        meta: {
          title: 'متغیرهای سیستم'
        }
      },
      {
        path: 'challenge',
        name: 'challenge-questions',
        component: ChallengeQuestions,
        meta: {
          title: 'چالش پرسش و پاسخ'
        }
      },
      {
        path: 'levels/:levelId/prize',
        name: 'levels-prize',
        component: LevelPrize,
        meta: {
          title: 'پاداش سطح'
        }
      },
      {
        path: 'levels/:levelId/licenses',
        name: 'levels-licenses',
        component: LevelLicenses,
        meta: {
          title: 'مجوزهای سطح'
        }
      },
      {
        path: 'levels/:levelId/gift',
        name: 'levels-gift',
        component: LevelGift,
        meta: {
          title: 'هدیه سطح'
        }
      },
      {
        path: 'levels/:levelId/general-info',
        name: 'levels-general-info',
        component: LevelGeneralInfo,
        meta: {
          title: 'اطلاعات کلی سطح'
        }
      },
      {
        path: 'levels/:levelId/gem',
        name: 'levels-gem',
        component: LevelGem,
        meta: {
          title: 'نگین سطح'
        }
      },
      {
        path: 'access-management/roles',
        name: 'roles',
        component: Roles,
        meta: {
          title: 'مدیریت نقش ها'
        }
      },
      {
        path: 'access-management/permissions',
        name: 'permissions',
        component: Permissions,
        meta: {
          title: 'مدیریت دسترسی ها'
        }
      },
      {
        path: 'access-management/employees',
        name: 'employee-role-permission',
        component: EmployeeRolePermission,
        meta: {
          title: 'مدیریت دسترسی کارمندان'
        }
      },
      {
        path: 'support/:department',
        name: 'support-department',
        component: SupportTickets,
        props: (route) => {
          const titleMap = {
            investment: 'پشتیبانی - سرمایه گذاری',
            citizens_safety: 'پشتیبانی - امنیت شهروندان',
            'citizens-safety': 'پشتیبانی - امنیت شهروندان',
            inspection: 'پشتیبانی - بازرسی',
            protection: 'پشتیبانی - حراست',
            technical_support: 'پشتیبانی فنی',
            'technical-support': 'پشتیبانی فنی',
            ztb: 'مدیریت کل ز.ت.ب'
          }
          return {
            department: route.params.department,
            pageTitle: titleMap[route.params.department] || 'پشتیبانی'
          }
        },
        meta: {
          title: 'پشتیبانی'
        }
      },
      {
        path: 'store/currencies',
        name: 'store-currencies',
        component: ColorsPrice,
        meta: {
          title: 'ارزها'
        }
      },
      {
        path: 'store/packages',
        name: 'store-packages',
        component: ColorOptions,
        meta: {
          title: 'بسته ها'
        }
      },
      {
        path: 'dynasty/messages',
        name: 'dynasty-messages',
        component: DynastyMessages,
        meta: {
          title: 'پیام های سلسله'
        }
      },
      {
        path: 'dynasty/permissions',
        name: 'dynasty-permissions',
        component: DynastyPermissions,
        meta: {
          title: 'دسترسی ها'
        }
      },
      {
        path: 'dynasty/prizes',
        name: 'dynasty-prizes',
        component: DynastyPrizes,
        meta: {
          title: 'جوایز سلسله'
        }
      },
      {
        path: 'maps',
        name: 'maps',
        component: MapsListing,
        meta: {
          title: 'لیست نقشه ها'
        }
      },
      {
        path: 'translations',
        name: 'translations-index',
        component: TranslationsIndex,
        meta: {
          title: 'مدیریت ترجمه‌ها'
        }
      },
      {
        path: 'isic-codes',
        name: 'isic-codes',
        component: IsicCodes,
        meta: {
          title: 'کدهای ISIC'
        }
      },
      {
        path: 'activity-logs',
        name: 'activity-logs',
        component: ActivityLogs,
        meta: {
          title: 'گزارش فعالیت‌ها'
        }
      },
      {
        path: 'translations/:translationId/modals',
        name: 'translations-modals',
        component: TranslationModals,
        props: true,
        meta: {
          title: 'مدیریت بخش‌های ترجمه'
        }
      },
      {
        path: 'translations/:translationId/modals/:modalId/tabs',
        name: 'translations-tabs',
        component: ModalTabs,
        props: true,
        meta: {
          title: 'مدیریت تب‌های ترجمه'
        }
      },
      {
        path: 'translations/:translationId/modals/:modalId/tabs/:tabId/fields',
        name: 'translations-fields',
        component: TabFields,
        props: true,
        meta: {
          title: 'عبارات ترجمه'
        }
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: {
      title: 'صفحه یافت نشد - 404'
    }
  }
]

const supportTitleMap = {
  investment: 'پشتیبانی - سرمایه گذاری',
  citizens_safety: 'پشتیبانی - امنیت شهروندان',
  'citizens-safety': 'پشتیبانی - امنیت شهروندان',
  inspection: 'پشتیبانی - بازرسی',
  protection: 'پشتیبانی - حراست',
  technical_support: 'پشتیبانی فنی',
  'technical-support': 'پشتیبانی فنی',
  ztb: 'مدیریت کل ز.ت.ب'
}

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  navigationProgress.start()

  if (to.meta.requiresAuth) {
    if (!hasValidAuthToken()) {
      clearAuthStorage()
      next({ name: 'login' })
      return
    }

    const { checkAuth } = useAuth()
    const needsFreshUser = from.name === 'login' || !from.name || to.name === 'forbidden'

    if (needsFreshUser || !localStorage.getItem('admin_user_data')) {
      try {
        const result = await checkAuth()
        if (!result?.authenticated) {
          clearAuthStorage()
          next({ name: 'login' })
          return
        }
      } catch {
        clearAuthStorage()
        next({ name: 'login' })
        return
      }
    }

    if (to.name === 'forbidden') {
      next()
      return
    }

    const routeAccess = resolveRouteAccess(to.path)
    if (routeAccess) {
      let userData = null
      try {
        userData = JSON.parse(localStorage.getItem('admin_user_data') || 'null')
      } catch {
        userData = null
      }

      if (!canAccess(routeAccess, userData)) {
        next({ name: 'forbidden' })
        return
      }
    }

    next()
    return
  }

  if (to.meta.requiresGuest) {
    if (hasValidAuthToken()) {
      next({ path: '/' })
      return
    }
  }

  next()
})

// Update page title on route change and finish progress
router.afterEach((to, from, failure) => {
  // If navigation failed, reset progress
  if (failure) {
    navigationProgress.reset()
    return
  }

  // Finish navigation progress
  navigationProgress.finish()

  const siteName = 'متارنگ'
  const title = to.name === 'support-department'
    ? supportTitleMap[to.params.department] || 'پشتیبانی'
    : (to.meta?.title || '')
  document.title = title ? `${title} - ${siteName}` : siteName
})

export default router

