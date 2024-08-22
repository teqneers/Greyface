"use strict";(self.webpackChunk_teqneers_greyface=self.webpackChunk_teqneers_greyface||[]).push([[257],{257:(e,t,a)=>{a.r(t),a.d(t,{default:()=>T});var n=a(4467),r=(a(4114),a(2953),a(6540)),l=a(5942),i=a(6347),c=a(5705),o=a(201),s=a(7839),m=a(6076),u=a(166),d=a(2693),b=(a(3110),a(2735)),h=a(4195),p=a(3096),y=a(8168),E=a(45),g=a(5378),f=a(8032),O=a(4479),S=a(1105),A=a(1100),k=a(5615),C=a(8084),v=a(1005),j=a(3043),w=a(4002);const P=["createMode","onSubmit","submitBtn","onCancel"];function B(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,n)}return a}function D(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?B(Object(a),!0).forEach((function(t){(0,n.A)(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):B(Object(a)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}const M=function(e){let{createMode:t=!0,onSubmit:a,submitBtn:n,onCancel:l}=e,i=(0,E.A)(e,P);const{t:c}=(0,h.Bd)();return r.createElement(C.l1,(0,y.A)({validateOnBlur:!0,validationSchema:(0,w.wB)(c),onSubmit:e=>{let n=e;t||(n=D(D({},e),{},{email:e.email[0]})),a.mutate(n)}},i),(e=>{let{handleSubmit:i,handleChange:o,values:s,errors:m,isSubmitting:u}=e;return r.createElement(g.A,{noValidate:!0,onSubmit:i},r.createElement(f.A.Body,null,r.createElement(O.A,{className:"mb-3"},r.createElement(C.ED,{name:"email",render:e=>{s.email&&0!==s.email.length||(s.email=[""]);const a=s.email.length;return r.createElement(r.Fragment,null,s.email.map(((n,l)=>{var i,u;return r.createElement(g.A.Group,{key:l,as:S.A,md:"12",className:"mt-2"},r.createElement(g.A.Label,null,c("blacklist.email.email")),r.createElement(A.A,null,r.createElement(g.A.Control,{type:"email",name:"email[".concat(l,"]"),value:s.email[l],onChange:o,isInvalid:m.email instanceof Array?!(null===(i=m.email)||void 0===i||!i[l]):!!m.email}),t&&a>1&&r.createElement(k.A,{variant:"outline-warning",onClick:()=>e.remove(l)},"X"),r.createElement(g.A.Control.Feedback,{type:"invalid"},m.email instanceof Array?null===(u=m.email)||void 0===u?void 0:u[l]:m.email)))})),t&&a<5&&r.createElement(k.A,{variant:"link",className:"mt-2 m-auto w-75",onClick:()=>e.push("")},c("placeholder.addMore")))}}))),r.createElement(f.A.Footer,null,r.createElement(v.A,{onClick:()=>l()}),r.createElement(j.A,{label:n,disabled:u&&!a.isError})))}))},F=e=>{let{onCancel:t,onCreate:a}=e;const[n,i]=(0,r.useState)(null),{t:o}=(0,h.Bd)(),{apiUrl:s}=(0,c.Y)(),m=(0,l.useMutation)((async e=>await fetch("".concat(s,"/opt-in/emails"),{method:"POST",body:JSON.stringify(e)}).then((function(e){if(!e.ok)throw e;return i(null),e})).then((e=>e.json())).catch((e=>{e.json().then((e=>{i(e.error)}))}))),{onSuccess:async()=>{a()}});return r.createElement(p.A,{title:"blacklist.email.addHeader",onHide:()=>t()},n&&r.createElement(b.A,{key:"danger",variant:"danger"},n),r.createElement(M,{initialValues:{email:[]},onSubmit:m,onCancel:t,submitBtn:o("button.save")}))};var H=a(2473),Q=a(914),U=a(4821);const N=e=>{let{onDelete:t,data:a}=e;const{t:n}=(0,h.Bd)(),{apiUrl:i}=(0,c.Y)(),[o,s]=(0,r.useState)(!1),m=(0,l.useMutation)((e=>fetch("".concat(i,"/opt-in/emails/delete"),{method:"DELETE",body:JSON.stringify({email:e.email})}).then((async e=>{const a=await e.json();if(!e.ok){const t=a&&a.message||e.status;return Promise.reject(t)}t()})).catch((e=>{console.error("There was an error!",e)}))));return r.createElement(r.Fragment,null,r.createElement(Q.A,{onClick:()=>s(!0)}),r.createElement(U.A,{show:o,onConfirm:()=>m.mutateAsync(a),onCancel:()=>s(!1),title:"blacklist.email.deleteHeader"},n("blacklist.email.deleteMessage")))},q=e=>{let{onUpdate:t,data:a}=e;const{t:n}=(0,h.Bd)(),{apiUrl:i}=(0,c.Y)(),[o,s]=(0,r.useState)(!1),[u,d]=(0,r.useState)(null),y=(0,l.useMutation)((async e=>await fetch("".concat(i,"/opt-in/emails/edit"),{method:"PUT",body:JSON.stringify({dynamicID:{email:a.email},email:e.email})}).then((function(e){if(!e.ok)throw e;return d(null),e})).then((e=>e.json())).catch((e=>{e.json().then((e=>{d(e.error)}))}))),{onSuccess:async()=>{t()}});return r.createElement(r.Fragment,null,r.createElement(m.A,{onClick:()=>s(!0),label:"button.edit"}),r.createElement(p.A,{show:o,title:"blacklist.email.editHeader",onHide:()=>s(!1)},u&&r.createElement(b.A,{key:"danger",variant:"danger"},u),r.createElement(M,{initialValues:{email:[a.email]},onSubmit:y,createMode:!1,onCancel:()=>s(!1),submitBtn:n("button.save")})))},x=e=>{let{data:t,refetch:a,isFetching:n,pageCount:l,initialState:i,onStateChange:c}=e;const{t:o}=(0,h.Bd)(),s=(0,r.useMemo)((()=>[{Header:o("blacklist.email.email"),id:"email",accessor:e=>e.email,canSort:!0,disableResizing:!0},{Header:"",id:"actions",width:100,minWidth:100,maxWidth:100,disableSortBy:!0,disableResizing:!0,Cell:e=>{let{row:{original:t}}=e;return r.createElement(r.Fragment,null,r.createElement(q,{onUpdate:a,data:t}),r.createElement(N,{onDelete:a,data:t}))}}]),[o,a]);return n?r.createElement(u.A,null):r.createElement(H.A,{idColumn:"email",data:t,pageCount:l,columns:s,disableSortRemove:!0,onStateChange:c,initialState:i})};function z(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,n)}return a}function I(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?z(Object(a),!0).forEach((function(t){(0,n.A)(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):z(Object(a)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}const T=()=>{var e;const t=(0,i.W6)(),{apiUrl:a}=(0,c.Y)(),{path:n,url:b}=(0,i.W5)(),{blacklistEmail:h}=(0,s.t0)(),[p,y]=(0,r.useState)(h),[E,g]=(0,r.useState)(null!==(e=h.searchQuery)&&void 0!==e?e:""),f=(0,r.useCallback)((e=>{(0,s.ZC)("blacklistEmail",I(I({},e),{},{searchQuery:E})),y((t=>I(I(I({},t),e),{},{searchQuery:E})))}),[E]);(0,r.useEffect)((()=>{const e=I(I({},p),{},{pageIndex:0,searchQuery:E});(0,s.ZC)("blacklistEmail",e),y(e)}),[E]);const{isLoading:O,isError:S,error:A,data:k,isFetching:C,refetch:v}=(0,l.useQuery)(["opt-in","emails",p,E],(()=>{let e="".concat(a,"/opt-in/emails?start=").concat(p.pageIndex,"&max=").concat(p.pageSize,"&query=").concat(E);return p.sortBy[0]&&(e+="&sortBy=".concat(p.sortBy[0].id,"&desc=").concat(p.sortBy[0].desc?1:0)),fetch(e).then((e=>e.json()))}),{keepPreviousData:!0});return O?r.createElement(u.A,null):r.createElement(o.A,{title:"blacklist.email.header"},r.createElement(d.A,{title:"blacklist.email.header",buttons:r.createElement(m.A,{label:"button.addEmail",onClick:()=>t.push("".concat(b,"/add"))}),searchQuery:E,setSearchQuery:g}),S?r.createElement("div",null,"Error: ",A):r.createElement(x,{data:k.results,refetch:v,pageCount:Math.ceil(k.count/p.pageSize),isFetching:C||O,initialState:p,onStateChange:f}),r.createElement(i.qh,{path:"".concat(n,"/add")},r.createElement(F,{onCancel:()=>t.push(b),onCreate:()=>{t.push(b),v()}})))}}}]);