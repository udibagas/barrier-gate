import Vue from 'vue'
import VueRouter from 'vue-router'
import { Message } from 'element-ui';
import Home from './pages/Home'
import AccessLog from './pages/AccessLog'
import Report from './pages/Report'
import Setting from './pages/Setting'
import Notification from './pages/Notification'

Vue.use(VueRouter)

const router = new VueRouter({
    routes: [
        { path: '/', component: Home, name: 'home' },
        { path: '/access-log', component: AccessLog, name: 'access-log' },
        { path: '/report', component: Report, name: 'report' },
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
                message: 'Anda tidak berhak mengakses halaman ini.',
                type: 'error',
                showClose: true,
                duration: 10000
            })
            next(false)
        })
    }
});

export default router
