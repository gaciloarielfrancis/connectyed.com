import{o as a,d as i,e,q as k,h as m,p as M,s as U,t as f,F as h,l as C,m as r,v as c,x as b,k as u,f as g,w as E,j as y}from"./app-DOCmqhjC.js";import{_ as x}from"./_plugin-vue_export-helper-DlAUqK2U.js";const P={props:["isOpen","pdfUrl"],methods:{closeModal(){this.$emit("close")}}},V=["src"],O={class:"flex justify-center"};function j(l,s,p,_,o,n){return p.isOpen?(a(),i("div",{key:0,class:"modal-overlay",onClick:s[2]||(s[2]=(...d)=>n.closeModal&&n.closeModal(...d))},[e("div",{class:"modal-content",onClick:s[1]||(s[1]=k(()=>{},["stop"]))},[e("iframe",{src:p.pdfUrl,width:"300px",height:"500px"},null,8,V),e("div",O,[e("button",{class:"bg-connectyed-button-light text-connectyed-button-dark py-4 px-1 w-24 justify-self-center",onClick:s[0]||(s[0]=(...d)=>n.closeModal&&n.closeModal(...d))},"Close")])])])):m("",!0)}const T=x(P,[["render",j]]),N={name:"register",components:{PdfModal:T},data(){return{user:{name:"",username:"",email:"",password:"",password_confirmation:"",privacypolicy:"",termsofuse:""},successMessage:"",validationErrors:{},isModalOpen:!1,pdfUrl:"",modalMode:{header:""},processing:!1}},methods:{...M({signIn:"auth/login"}),showPrivacy(){this.modalMode.header="Privacy Policy",this.pdfUrl="/upload/pdf/privacypolicy.pdf",this.isModalOpen=!0},showTerm(){this.modalMode.header="Terms of Use Agreement",this.pdfUrl="/upload/pdf/termsofuse.pdf",this.isModalOpen=!0},closeModal(){this.isModalOpen=!1,this.pdfUrl=""},async register(){this.processing=!0,await U.post("/api/user/register",this.user).then(l=>{l.data.success===!0?(this.successMessage=l.data.message,this.validationErrors={},setTimeout(()=>{this.$router.push({name:"login"})},1500)):this.validationErrors=l.data.data}).catch(({response:l})=>{l.status===422?this.validationErrors=l.data.errors:(this.validationErrors={},alert(l.data.message))}).finally(()=>{this.processing=!1})}}},A={class:"mx-auto max-w-7xl py-6 sm:px-6 lg:px-8 flex items-center justify-center"},B={class:"w-full max-w-sm"},I={key:0,class:"bg-green-200 text-green-800 p-4 mt-4 rounded absolute top-16"},R={class:"font-bold text-xl mb-2"},D=e("span",{class:"mr-3 mb-1"},"Register",-1),F={key:0,class:"float-right"},L=e("img",{class:"h-5 ml-3",src:"assets/images/icons/process.gif"},null,-1),S=[L],q={key:0,class:"mb-1"},z={class:"alert alert-danger"},G={class:"mb-0"},H={class:"mb-4"},J=e("label",{class:"block text-gray-700 text-sm font-bold mb-2",for:"name"}," Name ",-1),K={class:"mb-4"},Q=e("label",{class:"block text-gray-700 text-sm font-bold mb-2",for:"username"}," Username ",-1),W={class:"mb-4"},X=e("label",{class:"block text-gray-700 text-sm font-bold mb-2",for:"email"}," Email ",-1),Y={class:"mb-6"},Z=e("label",{class:"block text-gray-700 text-sm font-bold mb-2",for:"password"}," Password ",-1),$={class:"mb-2"},ee=e("label",{class:"block text-gray-700 text-sm font-bold mb-2",for:"password_confirmation"}," Confirm Password ",-1),se={class:"mb-1"},oe={class:"block text-gray-500 text-sm mb-2",for:"privacypolicy"},te={class:"mb-6"},le={class:"block text-gray-500 text-sm mb-2",for:"termsofuse"},ne={class:"flex items-center justify-between mb-6"},re=["disabled"],ae=e("p",{class:"text-center text-gray-500 text-xs"}," ©2024 Connectyed. ",-1);function ie(l,s,p,_,o,n){const d=y("router-link"),w=y("pdf-modal");return a(),i(h,null,[e("div",A,[e("div",B,[o.successMessage?(a(),i("div",I,f(o.successMessage),1)):m("",!0),e("div",R,[D,o.processing?(a(),i("span",F,S)):m("",!0)]),e("form",{class:"bg-connectyed-card-light shadow-md rounded px-8 pt-6 pb-8 mb-4 border-solid border-2 border-gray-200",action:"javascript:void(0)",onSubmit:s[10]||(s[10]=(...t)=>n.register&&n.register(...t)),method:"post"},[Object.keys(o.validationErrors).length>0?(a(),i("div",q,[e("div",z,[e("ul",G,[(a(!0),i(h,null,C(o.validationErrors,(t,v)=>(a(),i("li",{class:"text-red-500 text-xs italic",key:v},f(t[0]),1))),128))])])])):m("",!0),e("div",H,[J,r(e("input",{class:"appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline",type:"text",name:"name","onUpdate:modelValue":s[0]||(s[0]=t=>o.user.name=t),id:"name",placeholder:"Enter name",autocomplete:"off"},null,512),[[c,o.user.name]])]),e("div",K,[Q,r(e("input",{class:"appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline",type:"text",name:"username","onUpdate:modelValue":s[1]||(s[1]=t=>o.user.username=t),id:"username",placeholder:"Enter Username",autocomplete:"off"},null,512),[[c,o.user.username]])]),e("div",W,[X,r(e("input",{class:"appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline",type:"text",name:"email","onUpdate:modelValue":s[2]||(s[2]=t=>o.user.email=t),id:"email",placeholder:"Enter Email",autocomplete:"off"},null,512),[[c,o.user.email]])]),e("div",Y,[Z,r(e("input",{class:"appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline",type:"password",name:"password","onUpdate:modelValue":s[3]||(s[3]=t=>o.user.password=t),id:"password",placeholder:"Enter Password"},null,512),[[c,o.user.password]])]),e("div",$,[ee,r(e("input",{class:"appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline",type:"password",name:"password_confirmation","onUpdate:modelValue":s[4]||(s[4]=t=>o.user.password_confirmation=t),id:"password_confirmation",placeholder:"Confirm Password"},null,512),[[c,o.user.password_confirmation]])]),e("div",se,[e("label",oe,[r(e("input",{class:"w-4",id:"privacypolicy",name:"privacypolicy",type:"checkbox","onUpdate:modelValue":s[5]||(s[5]=t=>o.user.privacypolicy=t)},null,512),[[b,o.user.privacypolicy]]),u(" I have read and agree to the "),e("a",{onClick:s[6]||(s[6]=t=>n.showPrivacy()),class:"text-connectyed-link-dark"}," Privacy Policy ")])]),e("div",te,[e("label",le,[r(e("input",{class:"w-4",id:"termsofuse",name:"termsofuse",type:"checkbox","onUpdate:modelValue":s[7]||(s[7]=t=>o.user.termsofuse=t)},null,512),[[b,o.user.termsofuse]]),u(" I have read and agree to the "),e("a",{onClick:s[8]||(s[8]=t=>n.showTerm()),class:"text-connectyed-link-dark"}," Terms of Use. ")])]),e("div",ne,[e("button",{class:"bg-connectyed-button-light text-connectyed-button-dark hover:bg-connectyed-button-hover-light hover:text-connectyed-button-light w-full h-12 transition-colors duration-150 focus:shadow-outline font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline max-w-100",type:"submit",disabled:o.processing,onClick:s[9]||(s[9]=(...t)=>l.login&&l.login(...t))},f(o.processing?"Please wait":"Register"),9,re)]),e("label",null,[u("Already have an account? "),g(d,{to:{name:"login"}},{default:E(()=>[u("Login Now!")]),_:1})])],32),ae])]),g(w,{isOpen:o.isModalOpen,pdfUrl:o.pdfUrl,onClose:n.closeModal},null,8,["isOpen","pdfUrl","onClose"])],64)}const ue=x(N,[["render",ie]]);export{ue as default};
