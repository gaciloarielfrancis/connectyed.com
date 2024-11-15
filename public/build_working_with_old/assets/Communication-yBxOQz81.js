import{_ as w,M,x as g,e as i,f as t,i as u,n as b,k as _,v as y,F as m,z as p,L as C,t as c,g as x,m as D,o as r,p as h,q as T,s as z}from"./app-D0Fdxhbm.js";import{M as S,f as A,p as G}from"./Messaging-88m0BgJs.js";const I={name:"Communication",components:{Messaging:S},data(){return{currentTab:"schedule",selectedClients:[],clients:[],matchmakerMeetings:[],clientMeetings:[],processing:!1,searchQuery:"",showDropdown:!1,duration:30}},computed:{...M({user:e=>e.auth.user,authorization:e=>e.auth.authorization}),isAuthenticated(){return!!this.authorization&&!!this.authorization.token},filteredClients(){const e=this.searchQuery.toLowerCase();return this.clients.filter(s=>s.name&&s.name.toLowerCase().includes(e)&&!this.selectedClients.find(d=>d.id===s.id))},minDateTime(){return new Date().toISOString().slice(0,16)}},watch:{isAuthenticated(e){e&&(this.getClients(),this.getUpcomingMeetings())}},created(){const s=new URLSearchParams(window.location.search).get("token");s?(g.defaults.headers.common.Authorization=`Bearer ${s}`,g.get("/api/user/introspect",{headers:{Authorization:`Bearer ${s}`}}).then(d=>{const f={user:d.data.user,authorization:{token:s}};this.$store.dispatch("auth/setAuth",f),window.history.replaceState({},document.title,"/matchmaker/communication")}).catch(d=>{console.error("Error fetching user data:",d),alert("Failed to authenticate. Please log in again."),this.$router.push("/login")})):this.$store.dispatch("auth/initialize")},mounted(){this.isAuthenticated&&(this.getClients(),this.getUpcomingMeetings())},methods:{connectGoogle(){window.location.href="/api/google/redirect"},async revokeGoogle(){try{const e=await g.post("/api/google/revoke-token",{},{headers:{Authorization:`Bearer ${this.authorization.token}`}});e.data.success?(alert(e.data.message),this.$store.dispatch("auth/logout")):alert(e.data.message||"Failed to revoke Google account.")}catch(e){console.error("Error revoking Google account:",e),alert("An error occurred while revoking your Google account.")}},async getClients(){if(this.isAuthenticated){this.processing=!0;try{const e="/api/clients/all",{data:s}=await g.get(e,{headers:{Authorization:`Bearer ${this.authorization.token}`}});s.success?this.clients=s.data:alert(s.message||"Failed to fetch clients. Please try again.")}catch(e){console.error("Error fetching clients:",e.response||e),e.response&&e.response.status===403?alert("You are not authorized to view clients."):alert("Failed to fetch clients. Please try again.")}finally{this.processing=!1}}},async getUpcomingMeetings(){if(this.isAuthenticated){this.processing=!0;try{const e=await g.get("/api/google/upcoming-meetings",{headers:{Authorization:`Bearer ${this.authorization.token}`}});e.data.success?(this.matchmakerMeetings=e.data.data.matchmakerMeetings,this.clientMeetings=e.data.data.clientMeetings):console.error("Failed to fetch upcoming meetings:",e.data.message)}catch(e){console.error("Error fetching upcoming meetings:",e)}finally{this.processing=!1}}},selectClient(e){this.selectedClients.length<2?(this.selectedClients.push(e),this.searchQuery="",this.showDropdown=!1,this.$refs.clientInput.blur()):alert("You can select up to 2 clients only.")},removeClient(e){this.selectedClients=this.selectedClients.filter(s=>s.id!==e)},hideDropdown(){setTimeout(()=>{this.showDropdown=!1},100)},async scheduleMeeting(){if(this.selectedClients.length===0){alert("Please select at least one client.");return}if(!this.startTime){alert("Please select a meeting date and time.");return}if(!this.duration){alert("Please select a meeting duration.");return}if(new Date(this.startTime)<new Date){alert("Please select a future date and time for the meeting.");return}if(![15,30,60].includes(parseInt(this.duration))){alert("Invalid duration selected.");return}this.processing=!0;try{const s=await g.post("/api/google/create-meeting",{client_ids:this.selectedClients.map(d=>d.id),start_time:this.startTime,duration:parseInt(this.duration),matchmaker_id:this.user.id},{headers:{Authorization:`Bearer ${this.authorization.token}`}});s.data.success?(alert("Meeting scheduled successfully!"),this.selectedClients=[],this.startTime="",this.duration=30,this.getUpcomingMeetings()):alert(s.data.message||"Failed to schedule meeting.")}catch(s){console.error(s),alert("Failed to schedule meeting. Please try again.")}finally{this.processing=!1}},formatDate(e){return A(G(e),"EEEE, MMMM d, yyyy h:mm a")}}},l=e=>(T("data-v-0ebc7d43"),e=e(),z(),e),P={key:0},U={key:0,class:"mb-4"},B={key:0},F=l(()=>t("p",{class:"text-green-500"},"✅ Google Account Connected",-1)),E={key:1},L=l(()=>t("p",{class:"text-red-500"},"❌ Google Account Not Connected",-1)),N={class:"tabs mb-4"},V={key:1},Q={class:"mb-6"},Y=l(()=>t("h3",{class:"text-lg font-semibold mb-2"},"Select Clients",-1)),q={class:"relative"},J={key:0,class:"absolute z-10 w-full bg-white border rounded shadow mt-1 max-h-60 overflow-y-auto"},O=["onClick"],H={key:0,class:"px-3 py-2 text-gray-500"},R={class:"mt-2"},j=["onClick"],K={class:"mb-6"},W=l(()=>t("h3",{class:"text-lg font-semibold mb-2"},"Select Date and Time",-1)),X=["min"],Z={class:"mb-6"},$=l(()=>t("h3",{class:"text-lg font-semibold mb-2"},"Select Duration",-1)),ee=l(()=>t("option",{disabled:"",value:""},"Please select duration",-1)),te=l(()=>t("option",{value:"15"},"15 Minutes",-1)),se=l(()=>t("option",{value:"30"},"30 Minutes",-1)),oe=l(()=>t("option",{value:"60"},"60 Minutes",-1)),ne=[ee,te,se,oe],ie=["disabled"],re={class:"mt-8"},le={key:2},ae=l(()=>t("h3",{class:"text-lg font-semibold mb-4"},"Your Upcoming Meetings",-1)),ce={key:0,class:"text-center"},de={key:1},ue={key:0,class:"text-center text-gray-500"},he={key:1},ge={key:0,class:"mb-6"},me=l(()=>t("h4",{class:"text-md font-semibold mb-2"},"Hosted Meetings",-1)),pe=l(()=>t("strong",null,"Meeting ID:",-1)),_e=l(()=>t("strong",null,"Date & Time:",-1)),fe=l(()=>t("strong",null,"Status:",-1)),be=["href"],ye={key:1},ke=l(()=>t("h4",{class:"text-md font-semibold mb-2"},"Client Meetings",-1)),ve=l(()=>t("strong",null,"Meeting ID:",-1)),we=l(()=>t("strong",null,"Date & Time:",-1)),Me=l(()=>t("strong",null,"Status:",-1)),Ce=["href"],xe={key:1,class:"text-center py-10"},De=l(()=>t("p",null,"Loading...",-1)),Te=[De];function ze(e,s,d,f,n,a){const k=D("Messaging");return r(),i("div",null,[a.isAuthenticated?(r(),i("div",P,[e.user.role==="matchmaker"?(r(),i("div",U,[e.user.google_access_token?(r(),i("div",B,[F,t("button",{onClick:s[0]||(s[0]=(...o)=>a.revokeGoogle&&a.revokeGoogle(...o)),class:"py-2 px-4 bg-red-500 text-white rounded hover:bg-red-600"}," Disconnect Google Account ")])):(r(),i("div",E,[L,t("button",{onClick:s[1]||(s[1]=(...o)=>a.connectGoogle&&a.connectGoogle(...o)),class:"py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600"}," Connect Google Account ")]))])):u("",!0),t("div",N,[t("button",{onClick:s[2]||(s[2]=o=>n.currentTab="schedule"),class:b([{active:n.currentTab==="schedule"},"tab-button"])}," Schedule a Meeting ",2),t("button",{onClick:s[3]||(s[3]=o=>n.currentTab="upcoming"),class:b([{active:n.currentTab==="upcoming"},"tab-button"])}," Upcoming Meetings ",2)]),n.currentTab==="schedule"?(r(),i("div",V,[t("div",Q,[Y,t("div",q,[_(t("input",{"onUpdate:modelValue":s[4]||(s[4]=o=>n.searchQuery=o),onFocus:s[5]||(s[5]=o=>n.showDropdown=!0),onBlur:s[6]||(s[6]=(...o)=>a.hideDropdown&&a.hideDropdown(...o)),onInput:s[7]||(s[7]=o=>n.showDropdown=!0),type:"text",placeholder:"Search for clients...",class:"w-full px-3 py-2 border rounded",ref:"clientInput"},null,544),[[y,n.searchQuery]]),n.showDropdown?(r(),i("ul",J,[(r(!0),i(m,null,p(a.filteredClients,o=>(r(),i("li",{key:o.id,onClick:v=>a.selectClient(o),class:"px-3 py-2 hover:bg-gray-100 cursor-pointer"},c(o.name),9,O))),128)),a.filteredClients.length===0?(r(),i("li",H," No clients found. ")):u("",!0)])):u("",!0)]),t("div",R,[(r(!0),i(m,null,p(n.selectedClients,o=>(r(),i("span",{key:o.id,class:"inline-flex items-center bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded mr-2"},[h(c(o.name)+" ",1),t("button",{onClick:v=>a.removeClient(o.id),class:"ml-1 text-red-500"},"✖️",8,j)]))),128))])]),t("div",K,[W,_(t("input",{"onUpdate:modelValue":s[8]||(s[8]=o=>e.startTime=o),type:"datetime-local",class:"w-full px-3 py-2 border rounded",min:a.minDateTime},null,8,X),[[y,e.startTime]])]),t("div",Z,[$,_(t("select",{"onUpdate:modelValue":s[9]||(s[9]=o=>n.duration=o),class:"w-full px-3 py-2 border rounded"},ne,512),[[C,n.duration]])]),t("button",{onClick:s[10]||(s[10]=(...o)=>a.scheduleMeeting&&a.scheduleMeeting(...o)),disabled:n.processing||n.selectedClients.length===0||!e.startTime||!n.duration,class:"w-full py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 transition duration-200"},c(n.processing?"Scheduling...":"Schedule Meeting"),9,ie),t("div",re,[x(k,{currentUser:e.user,authorization:e.authorization},null,8,["currentUser","authorization"])])])):u("",!0),n.currentTab==="upcoming"?(r(),i("div",le,[ae,n.processing?(r(),i("div",ce,"Loading upcoming meetings...")):(r(),i("div",de,[n.matchmakerMeetings.length===0&&n.clientMeetings.length===0?(r(),i("div",ue," No upcoming meetings. ")):(r(),i("div",he,[n.matchmakerMeetings.length>0?(r(),i("div",ge,[me,t("ul",null,[(r(!0),i(m,null,p(n.matchmakerMeetings,o=>(r(),i("li",{key:o.id,class:"mb-2 p-4 border rounded"},[t("p",null,[pe,h(" "+c(o.google_meet_id),1)]),t("p",null,[_e,h(" "+c(a.formatDate(o.start_time)),1)]),t("p",null,[fe,h(" "+c(o.status),1)]),t("a",{href:o.google_meet_link,target:"_blank",class:"text-blue-500 underline"}," Join Google Meet ",8,be)]))),128))])])):u("",!0),n.clientMeetings.length>0?(r(),i("div",ye,[ke,t("ul",null,[(r(!0),i(m,null,p(n.clientMeetings,o=>(r(),i("li",{key:o.id,class:"mb-2 p-4 border rounded"},[t("p",null,[ve,h(" "+c(o.google_meet_id),1)]),t("p",null,[we,h(" "+c(a.formatDate(o.start_time)),1)]),t("p",null,[Me,h(" "+c(o.status),1)]),t("a",{href:o.google_meet_link,target:"_blank",class:"text-blue-500 underline"}," Join Google Meet ",8,Ce)]))),128))])])):u("",!0)]))]))])):u("",!0)])):(r(),i("div",xe,Te))])}const Ge=w(I,[["render",ze],["__scopeId","data-v-0ebc7d43"]]);export{Ge as default};