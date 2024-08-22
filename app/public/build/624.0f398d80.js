"use strict";(self.webpackChunk_teqneers_greyface=self.webpackChunk_teqneers_greyface||[]).push([[624],{3624:(e,t,n)=>{n.r(t),n.d(t,{default:()=>T});var a=n(4467),r=(n(4114),n(2953),n(6540)),o=n(5942),i=n(6347),c=n(201),l=n(5705),s=n(7839),m=n(6076),d=n(166),u=n(2693),h=(n(3110),n(2735)),p=n(4195),b=n(3096),y=n(8168),g=n(45),E=n(5378),f=n(8032),O=n(4479),S=n(1105),w=n(1100),A=n(5615),C=n(8084),v=n(1005),j=n(3043),k=n(4002);const P=["createMode","onSubmit","submitBtn","onCancel"];function D(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function B(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?D(Object(n),!0).forEach((function(t){(0,a.A)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):D(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}const M=function(e){let{createMode:t=!0,onSubmit:n,submitBtn:a,onCancel:o}=e,i=(0,g.A)(e,P);const{t:c}=(0,p.Bd)();return r.createElement(C.l1,(0,y.A)({validateOnBlur:!0,validationSchema:(0,k.a5)(c),onSubmit:e=>{let a=e;t||(a=B(B({},e),{},{domain:e.domain[0]})),n.mutate(a)}},i),(e=>{let{handleSubmit:i,handleChange:l,values:s,errors:m,isSubmitting:d}=e;return r.createElement(E.A,{noValidate:!0,onSubmit:i},r.createElement(f.A.Body,null,r.createElement(O.A,{className:"mb-3"},r.createElement(C.ED,{name:"domain",render:e=>{s.domain&&0!==s.domain.length||(s.domain=[""]);const n=s.domain.length;return r.createElement(r.Fragment,null,s.domain.map(((a,o)=>{var i,d;return r.createElement(E.A.Group,{key:o,as:S.A,md:"12",className:"mt-2"},r.createElement(E.A.Label,null,c("whitelist.domain.domain")),r.createElement(w.A,null,r.createElement(E.A.Control,{type:"text",name:"domain[".concat(o,"]"),value:s.domain[o],onChange:l,isInvalid:m.domain instanceof Array?!(null===(i=m.domain)||void 0===i||!i[o]):!!m.domain}),t&&n>1&&r.createElement(A.A,{variant:"outline-warning",onClick:()=>e.remove(o)},"X"),r.createElement(E.A.Control.Feedback,{type:"invalid"},m.domain instanceof Array?null===(d=m.domain)||void 0===d?void 0:d[o]:m.domain)))})),t&&n<5&&r.createElement(A.A,{variant:"link",className:"mt-2 m-auto w-75",onClick:()=>e.push("")},c("placeholder.addMore")))}}))),r.createElement(f.A.Footer,null,r.createElement(v.A,{onClick:()=>o()}),r.createElement(j.A,{label:a,disabled:d&&!n.isError})))}))},F=e=>{let{onCancel:t,onCreate:n}=e;const[a,i]=(0,r.useState)(null),{t:c}=(0,p.Bd)(),{apiUrl:s}=(0,l.Y)(),m=(0,o.useMutation)((async e=>await fetch("".concat(s,"/opt-out/domains"),{method:"POST",body:JSON.stringify(e)}).then((function(e){if(!e.ok)throw e;return i(null),e})).then((e=>e.json())).catch((e=>{e.json().then((e=>{i(e.error)}))}))),{onSuccess:async()=>{n()}});return r.createElement(b.A,{title:"whitelist.domain.addHeader",onHide:()=>t()},a&&r.createElement(h.A,{key:"danger",variant:"danger"},a),r.createElement(M,{initialValues:{domain:[]},onSubmit:m,onCancel:t,submitBtn:c("button.save")}))};var H=n(2473),Q=n(914),U=n(4821);const N=e=>{let{onDelete:t,data:n}=e;const{t:a}=(0,p.Bd)(),{apiUrl:i}=(0,l.Y)(),[c,s]=(0,r.useState)(!1),m=(0,o.useMutation)((e=>fetch("".concat(i,"/opt-out/domains/delete"),{method:"DELETE",body:JSON.stringify({domain:e.domain})}).then((async e=>{const n=await e.json();if(!e.ok){const t=n&&n.message||e.status;return Promise.reject(t)}t()})).catch((e=>{console.error("There was an error!",e)}))));return r.createElement(r.Fragment,null,r.createElement(Q.A,{onClick:()=>s(!0)}),r.createElement(U.A,{show:c,onConfirm:()=>m.mutateAsync(n),onCancel:()=>s(!1),title:"whitelist.domain.deleteHeader"},a("whitelist.domain.deleteMessage")))},q=e=>{let{onUpdate:t,data:n}=e;const{t:a}=(0,p.Bd)(),{apiUrl:i}=(0,l.Y)(),[c,s]=(0,r.useState)(!1),[d,u]=(0,r.useState)(null),y=(0,o.useMutation)((async e=>await fetch("".concat(i,"/opt-out/domains/edit"),{method:"PUT",body:JSON.stringify({dynamicID:{domain:n.domain},domain:e.domain})}).then((function(e){if(!e.ok)throw e;return u(null),e})).then((e=>e.json())).catch((e=>{e.json().then((e=>{u(e.error)}))}))),{onSuccess:async()=>{t()}});return r.createElement(r.Fragment,null,r.createElement(m.A,{label:"button.edit",onClick:()=>s(!0)}),r.createElement(b.A,{show:c,title:"whitelist.domain.editHeader",onHide:()=>s(!1)},d&&r.createElement(h.A,{key:"danger",variant:"danger"},d),r.createElement(M,{initialValues:{domain:[n.domain]},onSubmit:y,createMode:!1,onCancel:()=>s(!1),submitBtn:a("button.save")})))},x=e=>{let{data:t,refetch:n,isFetching:a,pageCount:o,initialState:i,onStateChange:c}=e;const{t:l}=(0,p.Bd)(),s=(0,r.useMemo)((()=>[{Header:l("whitelist.domain.domain"),id:"domain",accessor:e=>e.domain,canSort:!0,disableResizing:!0},{Header:"",id:"actions",disableSortBy:!0,disableResizing:!0,Cell:e=>{let{row:{original:t}}=e;return r.createElement(r.Fragment,null,r.createElement(q,{onUpdate:n,data:t}),r.createElement(N,{onDelete:n,data:t}))}}]),[l,n]);return a?r.createElement(d.A,null):r.createElement(H.A,{idColumn:"domain",data:t,pageCount:o,columns:s,disableSortRemove:!0,onStateChange:c,initialState:i})};function z(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function I(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?z(Object(n),!0).forEach((function(t){(0,a.A)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):z(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}const T=()=>{var e;const t=(0,i.W6)(),{apiUrl:n}=(0,l.Y)(),{path:a,url:h}=(0,i.W5)(),{whitelistDomain:p}=(0,s.t0)(),[b,y]=(0,r.useState)(p),[g,E]=(0,r.useState)(null!==(e=p.searchQuery)&&void 0!==e?e:""),f=(0,r.useCallback)((e=>{(0,s.ZC)("whitelistDomain",I(I({},e),{},{searchQuery:g})),y((t=>I(I(I({},t),e),{},{searchQuery:g})))}),[g]);(0,r.useEffect)((()=>{const e=I(I({},b),{},{pageIndex:0,searchQuery:g});(0,s.ZC)("whitelistDomain",e),y(e)}),[g]);const{isLoading:O,isError:S,error:w,data:A,isFetching:C,refetch:v}=(0,o.useQuery)(["opt-out","domains",b,g],(()=>{let e="".concat(n,"/opt-out/domains?start=").concat(b.pageIndex,"&max=").concat(b.pageSize,"&query=").concat(g);return b.sortBy[0]&&(e+="&sortBy=".concat(b.sortBy[0].id,"&desc=").concat(b.sortBy[0].desc?1:0)),fetch(e).then((e=>e.json()))}),{keepPreviousData:!0});return O?r.createElement(d.A,null):r.createElement(c.A,{title:"whitelist.domain.header"},r.createElement(u.A,{title:"whitelist.domain.header",buttons:r.createElement(m.A,{label:"button.addDomain",onClick:()=>t.push("".concat(h,"/add"))}),searchQuery:g,setSearchQuery:E}),S?r.createElement("div",null,"Error: ",w):r.createElement(x,{data:A.results,refetch:v,pageCount:Math.ceil(A.count/b.pageSize),isFetching:C||O,initialState:b,onStateChange:f}),r.createElement(i.qh,{path:"".concat(a,"/add")},r.createElement(F,{onCancel:()=>t.push(h),onCreate:()=>{t.push(h),v()}})))}}}]);