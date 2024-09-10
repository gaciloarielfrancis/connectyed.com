import axios from 'axios'
import router from '@/router'

export default {
    namespaced: true,
    state:{
        authenticated:false,
        user:{},
        authorization:{}
    },
    getters:{
        authenticated(state){
            return state.authenticated
        },
        user(state){
            return state.user
        }
    },
    mutations:{
        SET_AUTHENTICATED (state, value) {
            state.authenticated = value
        },
        SET_USER (state, value) {
            state.user = value
        },
        SET_TOKEN (state, value) {
            state.authorization = value
        }
    },
    actions:{
        login({commit}){ 
            return axios.post('/api/user/refresh').then(({ data }) => {                     
                //const localUser = JSON.parse(localStorage.getItem('user'));
                localStorage.removeItem("user")
                localStorage.setItem("user", JSON.stringify(data)) 
                const localUser = JSON.parse(localStorage.getItem('user'))                
                commit('SET_USER', localUser.data)
                commit('SET_AUTHENTICATED',true)
                commit('SET_TOKEN',localUser.authorization)
                router.push({name:'dashboard'})
            }).catch(({response:{data}})=>{
                commit('SET_USER',{})
                commit('SET_AUTHENTICATED',false)
                commit('SET_TOKEN',{})
            })
        },
        logout({commit}){
            commit('SET_USER',{})
            commit('SET_AUTHENTICATED',false)
            commit('SET_TOKEN',{})
            localStorage.removeItem("user")
            localStorage.removeItem("vuex")
        }
    }
}