import Vue from 'vue'
import VueRouter from 'vue-router'
import { Message } from 'element-ui';
import Home from './pages/Home'
import AccessLog from './pages/AccessLog'
import KarcisHilang from './pages/KarcisHilang'
import BukaManual from './pages/BukaManual'
import Report from './pages/Report'
import Setting from './pages/Setting'
import Notification from './pages/Notification'
import Pos from './pages/Pos'
import Snapshot from './pages/Snapshot'

Vue.use(VueRouter)

const router = new VueRouter({
    routes: [
        { path: '/', component: Home, name: 'home' },
        { path: '/pos', component: Pos, name: 'pos' },
        { path: '/access-log', component: AccessLog, name: 'access-log' },
        { path: '/karcis-hilang', component: KarcisHilang, name: 'karcis-hilang' },
        { path: '/buka-manual', component: BukaManual, name: 'buka-manual' },
        { path: '/report', component: Report, name: 'report' },
        { path: '/snapshot', component: Snapshot, name: 'snapshot' },
        { path: '/setting', component: Setting, name: 'setting' },
        { path: '/notification', component: Notification, name: 'notification' },
        { path: '*', component: Home },
    ]
})

router.beforeEach((to, from, next) => {
    if (to.path == '/') {
        next()
    }

    else {
        let params = { route: to.path }
        axios.get('/checkAuth', { params: params }).then(r => {
            next()
        }).catch(e => {
            Message({
                message: 'Anda tidak berhak mengakses halaman ini atau sesi Anda telah habis. Silakan login ulang',
                type: 'error',
                showClose: true,
                duration: 10000
            })
            next(false)
        })
    }
});

export default router
