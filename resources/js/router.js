import Vue from 'vue'
import VueRouter from 'vue-router'
import { Message } from 'element-ui';
import Home from './pages/Home'
import Login from './pages/Login'
import AccessLog from './pages/AccessLog'
import KarcisHilang from './pages/KarcisHilang'
import BukaManual from './pages/BukaManual'
import Setting from './pages/Setting'
import Notification from './pages/Notification'
import Pos from './pages/Pos'
import Snapshot from './pages/Snapshot'
import Department from './pages/Department'
import User from './pages/User'

Vue.use(VueRouter)

const router = new VueRouter({
    routes: [
        { path: '/', component: Home, name: 'home' },
        { path: '/login', component: Login, name: 'login', meta: { layout: 'login' } },
        { path: '/pos', component: Pos, name: 'pos' },
        { path: '/access-log', component: AccessLog, name: 'access-log' },
        { path: '/karcis-hilang', component: KarcisHilang, name: 'karcis-hilang' },
        { path: '/buka-manual', component: BukaManual, name: 'buka-manual' },
        { path: '/snapshot', component: Snapshot, name: 'snapshot' },
        { path: '/setting', component: Setting, name: 'setting' },
        { path: '/department', component: Department, name: 'department' },
        { path: '/user', component: User, name: 'user' },
        { path: '/notification', component: Notification, name: 'notification' },
        { path: '*', component: Home },
    ]
})

router.beforeEach((to, from, next) => {
    if (to.path != '/login') {
        let params = { route: to.path }
        axios.get('/checkAuth', { params: params }).then(r => {
            next()
        }).catch(e => {
            var message = ''

            if (e.response.status == 403) {
                message = e.response.data.message
                next(false)
            }

            else if (e.response.status == 401) {
                message = 'Sesi Anda telah habis. Silakan login ulang'
                next('/login')
            }

            else {
                message = 'Unhandled error';
                next(false);
            }

            Message({
                message,
                type: 'error',
                showClose: true,
                duration: 10000
            })
        })
    } else {
        next()
    }
});

export default router
