// resources/js/router/index.js

import { createWebHistory, createRouter } from 'vue-router'
import store from '@/store'

/* Guest Components */
const GuestLayout = () => import('@/views/Templates/LayoutGuest.vue')
const Login = () => import('@/views/Login.vue')
const Register = () => import('@/views/Register.vue')
const Home = () => import('@/views/Home.vue')
const BlogDetail = () => import('@/views/BlogDetail.vue')
const ProfileDetail = () => import('@/views/ProfileDetail.vue')
const EmailVerification = () => import('@/components/EmailVerification.vue')
const ForgotPassword = () => import('@/components/ForgotPasswordForm.vue')
const ResetPassword = () => import('@/components/ResetPasswordForm.vue')
const Unauthorized = () => import('@/views/Unauthorized.vue')
const NotFound = () => import('@/views/NotFound.vue')
const GuestBilling = () => import('@/views/Billing.vue')
// const ZoomCallback = () => import('@/views/ZoomCallback.vue')

/* Clients */
const ClientDashboard = () => import('@/views/Client/Dashboard.vue')
const ClientLayout = () => import('@/views/Client/Layout.vue')
const ClientOverview = () => import('@/views/Client/Overview.vue')
const ClientProfile = () => import('@/views/Client/Profile.vue')
const ClientMessage = () => import('@/views/Client/Message.vue')
const ClientBilling = () => import('@/views/Client/Billing.vue')
const ClientCommunication = () => import('@/views/Client/Communication.vue')
const ClientCriteria = () => import('@/views/Client/CriteriaManagement.vue') // Added Import
const MatchMakerAvailability = () => import('@/views/Client/MatchMakerAvailability.vue')

/* Matchmaker */
const MatchmakerDashboard = () => import('@/views/Matchmaker/Dashboard.vue')
const MatchmakerLayout = () => import('@/views/Matchmaker/Layout.vue')
const MatchmakerOverview = () => import('@/views/Matchmaker/Overview.vue')
const MatchmakerProfile = () => import('@/views/Matchmaker/Profile.vue')
const MatchmakerSpecialties = () => import('@/views/Matchmaker/Specialties.vue')
const MatchmakerMatchManagement = () => import('@/views/Matchmaker/MatchManagement.vue')
const MatchmakerClients = () => import('@/views/Matchmaker/Clients.vue')
const MatchmakerAvailability= () => import('@/views/Matchmaker/Availability.vue')
const MatchmakerBilling = () => import('@/views/Matchmaker/Billing.vue')
const MatchmakerCommunication = () => import('@/views/Matchmaker/Communication.vue')
const MatchmakerRequestMeeting = () => import('@/views/Matchmaker/RequestMeeting.vue')

/* Admin */
const AdminLayout = () => import('@/views/Admin/Layout.vue')
const AdminDashboard = () => import('@/views/Admin/Dashboard.vue')
const AdminOverview = () => import('@/views/Admin/Overview.vue')
const AdminMatchmakers = () => import('@/views/Admin/Matchmakers.vue')
const AdminClients = () => import('@/views/Admin/Clients.vue')

const routes = [
    {
        path: '/email/verify',
        name: '',
        component: GuestLayout,
        meta: {
            middleware: "guest"
        },
        children: [
            {
                name: "EmailVerification",
                path: '/email/verify',
                component: EmailVerification,
                props: true,
                meta: {
                    title: `Email Verification`
                },
                props: route => ({
                    verificationUrl: route.query.verification_url
                }),
            }
        ]
    },
    {
        path: '/password',
        name: '',
        component: GuestLayout,
        meta: {
            middleware: "guest"
        },
        children: [
            {
                name: "ForgotPassword",
                path: 'forgot',
                component: ForgotPassword,
                props: true,
                meta: {
                    title: `Forgot Password`
                }
            },
            {
                name: "ResetPassword",
                path: 'reset/:hash',
                component: ResetPassword,
                props: true,
                meta: {
                    title: `Reset Password`
                }
            }
        ]
    },
    {
        path: "/",
        component: GuestLayout,
        meta: {
            middleware: "guest"
        },
        children: [
            {
                name: "home",
                path: '/',
                component: Home,
                props: true,
                meta: {
                    title: `Home`
                }
            }
        ]
    },
        {
        path: "/billing",
        component: GuestLayout,
        meta: {
            middleware: "guest"
        },
        children: [
            {
                name: "billing",
                path: '/billing',
                component: GuestBilling,
                props: true,
                meta: {
                    title: `Billing`
                }
            }
        ]
    },
    {
        path: "/login",
        component: GuestLayout,
        meta: {
            middleware: "guest"
        },
        children: [
            {
                name: "login",
                path: '/login',
                component: Login,
                meta: {
                    title: `Login`
                }
            }
        ]
    },
    {
        path: "/register",
        component: GuestLayout,
        meta: {
            middleware: "guest"
        },
        children: [
            {
                name: "register",
                path: '/register',
                component: Register,
                meta: {
                    title: `Register`
                }
            }
        ]
    },
    {
        path: "/tips/:slug",
        component: GuestLayout,
        meta: {
            middleware: "guest"
        },
        children: [
            {
                name: "blogdetail",
                path: '/tips/:slug',
                component: BlogDetail,
                meta: {
                    title: `Blog Detail`
                }
            }
        ]
    },
    {
        path: "/:username",
        component: GuestLayout,
        meta: {
            middleware: "guest"
        },
        children: [
            {
                name: "profiledetail",
                path: '/:username',
                component: ProfileDetail,
                meta: {
                    title: `Profile Detail`
                }
            }
        ]
    },
    {
        name: "admin",
        path: "/admin",
        component: AdminLayout,
        meta: {
            middleware: "auth",
            title: "Admin Dashboard",
            role: "admin"
        },
        children: [
            {
                props: true,
                name: "admin-dashboard",
                path: '',
                component: AdminDashboard,
                meta: {
                    title: `Admin Dashboard`
                },
                children: [
                    {
                        path: 'dashboard',
                        component: AdminOverview
                    },
                    {
                        path: 'matchmakers',
                        component: AdminMatchmakers
                    },
                    {
                        path: 'clients',
                        component: AdminClients
                    }
                ]
            }
        ]
    },
    {
        name: "matchmaker",
        path: "/matchmaker",
        component: MatchmakerLayout,
        meta: {
            middleware: "auth",
            title: "Matchmaker Dashboard",
            role: ['matchmaker', 'candidate']
        },
        children: [
            {
                props: true,
                name: "matchmaker-dashboard",
                path: '',
                component: MatchmakerDashboard,
                meta: {
                    title: `Matchmaker Dashboard`
                },
                children: [
                    {
                        path: 'dashboard',
                        component: MatchmakerOverview
                    },
                    {
                      path: 'specialties',
                      component: MatchmakerSpecialties
                    },
                    {
                      path: 'profile',
                      component: MatchmakerProfile
                    },
                    {
                        path: 'clients',
                        component: MatchmakerClients
                    },
                    {
                        path: 'availability',
                        component: MatchmakerAvailability
                    },
                    {
                        path: 'billing',
                        component: MatchmakerBilling
                    },
                    {
                        path: 'communication',
                        component: MatchmakerCommunication
                    },
                    {
                        path: 'match-management',
                        component: MatchmakerMatchManagement
                    },
                    {
                        name: 'matchmaker/request-meeting',
                        path: 'request-meeting',
                        component: MatchmakerRequestMeeting
                    }
                  ]
            }
        ]
    },
    {
        name: "client",
        path: "/client",
        component: ClientLayout,
        meta: {
            middleware: "auth",
            title: "Client Dashboard",
            role: "client"
        },
        children: [
            {
                props: true,
                name: "client-dashboard",
                path: '',
                component: ClientDashboard,
                meta: {
                    title: `Client Dashboard`
                },
                children: [
                    {
                        path: 'dashboard',
                        component: ClientOverview
                    },
                    {
                        path: 'profile',
                        component: ClientProfile
                    },
                    {
                        path: 'message',
                        component: ClientMessage
                    },
                    {
                        path: 'billing',
                        component: ClientBilling
                    },
                    {
                        path: 'communication',
                        component: ClientCommunication
                    },
                    {
                        path: 'matchmakeravailability',
                        component: MatchMakerAvailability
                    },
                    // Added Criteria Route
                    {
                        path: 'criteria',
                        component: ClientCriteria,
                        meta: {
                            requiresPackage: true
                        }
                    }
                ]
            }
        ]
    },
    {
        path: "/unauthorized",
        name: "unauthorized",
        component: GuestLayout,
        meta: {
            middleware: "guest",
            title: "Unauthorized",
        },
        children: [
            {
                name: "unauthorized",
                path: '/unauthorized',
                component: Unauthorized,
                meta: {
                    title: `Unauthorized`
                }
            }
        ]
    },
    {
        path: "/notfound",
        name: "notfound",
        component: GuestLayout,
        meta: {
            middleware: "guest",
            title: "Not Found",
        },
        children: [
            {
                name: "notfound",
                path: '/notfound',
                component: NotFound,
                meta: {
                    title: `Not Found`
                }
            }
        ]
    },
    {
        // path: '/zoom/callback',
        // name: 'ZoomCallback',
        // component: ZoomCallback,
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

// Updated beforeEach Navigation Guard
router.beforeEach(async (to, from, next) => {
    // Wait for auth store to be initialized
    if (!store.getters['auth/isInitialized']) {
        await store.dispatch('auth/initialize');
    }

    const isAuthenticated = store.getters['auth/authenticated'];
    const userRole = store.getters['auth/userRole'];
    // Fix: Access user directly from state instead of nested structure
    const user = store.state.auth.user;

    // Force refresh user data when accessing criteria
    if (to.path === '/client/criteria') {
        try {
            await store.dispatch('auth/getUser');
            // Fix: Access updated user directly
            const updatedUser = store.state.auth.user;
            
            if (!updatedUser?.purchased_package) {
                return next({ path: '/client/billing' });
            }
            return next();
        } catch (error) {
            console.error('Error fetching user data:', error);
            return next({ path: '/client/billing' });
        }
    }

    // Check for package requirement
    if (to.matched.some(record => record.meta.requiresPackage)) {
        if (!user?.purchased_package) {
            return next({ path: '/client/billing' });
        }
    }

    // Guest Middleware
    if (to.meta.middleware === 'guest') {
        if (isAuthenticated) {
            if (to.name === 'login') {
                if (userRole === 'admin') {
                    return next({ path: '/admin/dashboard' });
                } else if (userRole === 'matchmaker' || userRole === 'candidate') {
                    return next({ path: '/matchmaker/dashboard' });
                } else if (userRole === 'client') {
                    return next({ path: '/client/dashboard' });
                }
            } else {
                return next();
            }
        }
        return next();
    }

    // Auth Middleware
    if (to.meta.middleware === 'auth') {
        if (!isAuthenticated) {
            return next({ name: 'login' });
        }
        if (to.meta.role) {
            if (userRole === to.meta.role || (Array.isArray(to.meta.role) && to.meta.role.includes(userRole))) {
                return next();
            } else {
                return next({ name: 'unauthorized' });
            }
        }
    }

    // Proceed to the route if no middleware issues
    next();

})

export default router
