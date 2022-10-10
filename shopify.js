! function(e) {
    let t = {};

    function n(o) {
        if (t[o]) return t[o].exports;
        var r = t[o] = {
            i: o,
            l: !1,
            exports: {}
        };
        return e[o].call(r.exports, r, r.exports, n), r.l = !0, r.exports
    }
    n.m = e,
        n.c = t,
        n.d = function(e, t, o) {
            n.o(e, t) || Object.defineProperty(e, t, {
                enumerable: !0,
                get: o
            })
        },
        n.r = function(e) {
            "undefined" != typeof Symbol &&
            Symbol.toStringTag &&
            Object.defineProperty(e, Symbol.toStringTag, {
                value: "Module"
            }),
                Object.defineProperty(e, "__esModule", {
                    value: !0
                })
        },
        n.t = function(e, t) {
            if (1 & t && (e = n(e)), 8 & t)
                return e;
            if (4 & t && "object" == typeof e && e && e.__esModule)
                return e;
            var o = Object.create(null);
            if (
                n.r(o),
                    Object.defineProperty(o, "default", {
                        enumerable: !0,
                        value: e
                    }),
                2 & t && "string" != typeof e
            )
                for (var r in e) n.d(o, r, function(t) {
                    return e[t]
                }.bind(null, r));
            return o
        },
        n.n = function(e) {
            var t = e && e.__esModule ? function() {
                return e.default
            } : function() {
                return e
            };
            return n.d(t, "a", t), t
        },
        n.o = function(e, t) {
            return Object.prototype.hasOwnProperty.call(e, t)
        },
        n.p = "", n(n.s = 860)
}({
    10: function(e, t, n) {
        "use strict";
        t.a = {
            baseURL: "https://bp.rome9.com/",
            key_apikey: "data-api",
            key_payment_name: "AirSwiftPay",
        }
    },
    860: function(e, t, n) {
        "use strict";
        n.r(t);
        let o = n(10),
            r = function() {
                let n = document.createElement("button"), r = document.createElement("img");
                 r.src = o.a.baseURL+"static/imgs/bp-logo.png"
                    r.alt = "Pay with NOWPayments",
                    n.appendChild(r),
                    n.style = "width:100px;height:30px;outline:none",
                    n.onclick = function() {
                        let formData = {
                            app_key:document.querySelector("#airswift_paynemt").getAttribute(o.a.key_apikey),
                            order_id:window.Shopify.checkout.order_id,
                        };

                        fetch(o.a.baseURL+'api-create_payment',{
                            method:'post',
                            headers:{
                                "content-type":"application/json;charset=utf-8"
                            },
                            body: JSON.stringify(formData)
                        })
                            .then(function (data){
                            return data.json();
                        })
                            .then(function (res){
                                console.log('res',res);
                            // let res = json_to_obj(rr);
                            if(res.code != 1){
                                alert(res.msg)
                                return false;
                            }
                            else{
                                window.location.href = res.data;
                            }
                        })
                            .catch()

                    };
                return n
            };
        document.addEventListener("DOMContentLoaded", (function() {
            console.log('aa',Shopify.checkout);
            let e, t, n, i = document.querySelector(".payment-method-list__item__info");
            if (i && i.textContent && i.textContent.toLowerCase().includes(o.a.key_payment_name.toLowerCase())) {
                let a = {
                        mainHeader: document.querySelector("#main-header"),
                        orderConfirmed: document.querySelector(".os-step__title"),
                        orderConfirmedDescription: document.querySelector(".os-step__description"),
                        continueButton: document.querySelector(".step__footer__continue-btn"),
                        checkMarkIcon: document.querySelector(".os-header__hanging-icon"),
                    },
                    d = document.querySelector("#airswift_paynemt").getAttribute(o.a.key_apikey);
                if (d === null || d.length <= 0){
                    return a.mainHeader.innerText = "Choose another payment method",
                        e = a.orderConfirmed,
                        t = "Invalid API key provided, contact support",
                        (n = document.createElement("p")).innerText = t,
                        n.style.color = "#dc1d2e",
                        void e.after(n);
                }
                let els_order = document.querySelector(".os-timeline-step__title");
                if( els_order === null ){
                    //els_order.firstChild.data === 'Confirmed';
                  /*todo  let order_status = document.querySelector("#main-header").innerText;
                    if(order_status === 'Order canceled'){
                        return ;
                    }*/
                    (function(e) {
                        document.title = document.title.replace("Thank you", "Review and pay"),
                        e.mainHeader && (e.mainHeader.innerText = "Review and pay!"),
                        e.continueButton && (e.continueButton.style.visibility = "hidden"),
                        e.checkMarkIcon && (e.checkMarkIcon.style.visibility = "hidden"),
                        e.orderConfirmed && (e.orderConfirmed.style.display = "none"),
                        e.orderConfirmedDescription && (e.orderConfirmedDescription.style.display = "none")
                    }(a))
                    a.orderConfirmed.after(r());
                }

            }
        }))
    }
});