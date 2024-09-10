import{u as y,a as w,b as k,c as x,r as N,d as i,e,f as t,w as o,g as v,h as _,F as C,i as b,j as f,o as l,k as n}from"./app-D-UODoHf.js";const A={class:"header",id:"navbar"},B=b('<div class="d-none d-lg-block bg-connectyed-header-light"><div class="container"><div class="header__top--area"><div class="header__top--left"><ul><li><i class="fa-solid fa-phone"></i> <span>+800-123-4567 6587</span></li><li><i class="fa-solid fa-location-dot"></i> Beverley, New York 224 USA </li></ul></div><div class="header__top--right"><ul><li><a href="#"><i class="fa-brands fa-facebook-f"></i> Facebook</a></li><li><a href="#"><i class="fa-brands fa-instagram"></i> Instagram</a></li><li><a href="#"><i class="fa-brands fa-youtube"></i> Youtube</a></li></ul></div></div></div></div>',1),S={class:"header__bottom"},R={class:"container"},j={class:"navbar navbar-expand-lg"},I={class:"navbar-brand",href:"#"},V=e("img",{src:"assets/images/logo/connectyedlogo.png",alt:"Connectyed Logo",class:"w-[70px]"},null,-1),F=e("button",{class:"navbar-toggler collapsed",type:"button","data-bs-toggle":"collapse","data-bs-target":"#navbarNavAltMarkup","aria-controls":"navbarNavAltMarkup","aria-expanded":"false","aria-label":"Toggle navigation"},[e("span",{class:"navbar-toggler--icon"})],-1),L={class:"navbar-collapse justify-content-end",id:"navbarNavAltMarkup"},U={class:"navbar-nav mainmenu"},z={href:"#"},M={href:"#"},T={href:"#"},Y={href:"#"},D={class:"header__more"},E={key:0,class:"btn",type:"button"},J={key:1,class:"btn",type:"button"},H=b('<footer class="footer footer--style2"><div class="footer__top bg_img"><div class="footer__newsletter wow fadeInUp" data-wow-duration="1.5s"><div class="container"><div class="row g-4 justify-content-center"><div class="col-lg-6 col-12"><div class="footer__newsletter--area"><div class="footer__newsletter--title"><h4>Newsletter Sign up</h4></div><div class="footer__newsletter--form"><form action="#"><input type="email" placeholder="Your email address"><button type="submit" class="default-btn"><span>Subscribe</span></button></form></div></div></div><div class="col-lg-6 col-12"><div class="footer__newsletter--area justify-content-xxl-end"><div class="footer__newsletter--title me-xl-4"><h4>Join Community</h4></div><div class="footer__newsletter--social"><ul><li><a href="#"><i class="fa-brands fa-twitter"></i></a></li><li><a href="#"><i class="fa-brands fa-twitch"></i></a></li><li><a href="#"><i class="fa-brands fa-instagram"></i></a></li><li><a href="#"><i class="fa-brands fa-dribbble"></i></a></li><li><a href="#"><i class="fa-brands fa-facebook-messenger"></i></a></li></ul></div></div></div></div></div></div><div class="footer__toparea padding-top padding-bottom wow fadeInUp" data-wow-duration="1.5s"><div class="container"><div class="row g-4"><div class="col-lg-3 col-sm-6 col-12"><div class="footer__item footer--about"><div class="footer__inner"><div class="footer__content"><div class="footer__content--title"><h4>About Connectyed</h4></div><div class="footer__content--desc"><p>Connectyed is a matchmaking platform that connects people with ideal partners using advanced technology and expert matchmakers. Whether for friendship, love, or networking, we offer a trusted and easy-to-use service.</p></div><div class="footer__content--info"><p><b>Address :</b> 3rd Floor Tropical Building Elephant Road 1205</p><p><b>Contact :</b> connectyed.com</p></div></div></div></div></div></div></div></div></div><div class="footer__bottom wow fadeInUp" data-wow-duration="1.5s"><div class="container"><div class="footer__content text-center"><p class="mb-0">All Rights Reserved © <a href="index.html">Connectyed</a> || Design By: Connectyed</p></div></div></div></footer>',1),q={__name:"Layout",setup(O){const r=y(),c=w(),g=k(),d=x(()=>g.name),u=N(r.state.auth),h=u.user,p=async()=>{u.authorization.token,await axios.post("/api/user/logout").then(({data:s})=>{localStorage.getItem("user",JSON.stringify(s)),axios.defaults.headers.common.Authorization=`Bearer ${s.authorization.token}`,r.dispatch("auth/logout"),c.push({name:"home"})}).catch(s=>{console.log("data2",s),console.error(s),r.dispatch("auth/logout"),c.push({name:"home"})})};return(s,W)=>{const a=f("router-link"),m=f("router-view");return l(),i(C,null,[e("header",A,[B,e("div",S,[e("div",R,[e("nav",j,[e("a",I,[e("h2",null,[t(a,{to:{name:"home"},class:"nav-link"},{default:o(()=>[V]),_:1})])]),F,e("div",L,[e("div",U,[e("ul",null,[e("li",null,[e("a",z,[t(a,{to:{name:"home"},class:"nav-link hidden"},{default:o(()=>[n("Home")]),_:1})])]),e("li",null,[e("a",M,[t(a,{to:{name:"home"},class:"nav-link hidden"},{default:o(()=>[n("Blogs")]),_:1})])]),e("li",null,[e("a",T,[t(a,{to:{name:"home"},class:"nav-link hidden"},{default:o(()=>[n("Shops")]),_:1})])]),e("li",null,[e("a",Y,[t(a,{to:{name:"dashboard"},class:"nav-link"},{default:o(()=>[n("Dashboard")]),_:1})])])])]),e("div",D,[d.value=="login"?(l(),i("button",E,[t(a,{to:{name:"register"},class:"bg-connectyed-button-light text-connectyed-button-dark hover:bg-connectyed-button-hover-light hover:text-connectyed-button-light py-4 px-5"},{default:o(()=>[n("Register")]),_:1})])):d.value=="register"||!v(h).user.name?(l(),i("button",J,[t(a,{to:{name:"login"},class:"bg-connectyed-button-light text-connectyed-button-dark hover:bg-connectyed-button-hover-light hover:text-connectyed-button-light py-4 px-5"},{default:o(()=>[n("Login")]),_:1})])):_("",!0),v(h).user.name?(l(),i("a",{key:2,onClick:p,href:"javascript:void(0)",class:"bg-connectyed-button-light text-connectyed-button-dark hover:bg-connectyed-button-hover-light hover:text-connectyed-button-light py-4 px-5","aria-current":"page"},"Logout")):_("",!0)])])])])])]),e("main",null,[t(m)]),H],64)}}};export{q as default};
