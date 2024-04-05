! function(e) {
    "undefined" == typeof e.fn.each2 && e.extend(e.fn, {
        each2: function(t) {
            for (var s = e([0]), i = -1, n = this.length; ++i < n && (s.context = s[0] = this[i]) && t.call(s[0], i, s) !== !1;);
            return this
        }
    })
}(jQuery),
function(e, t) {
    "use strict";

    function s(e) {
        var t, s, i, n;
        if (!e || e.length < 1) return e;
        for (t = "", s = 0, i = e.length; i > s; s++) n = e.charAt(s), t += z[n] || n;
        return t
    }

    function i(e, t) {
        for (var s = 0, i = t.length; i > s; s += 1)
            if (o(e, t[s])) return s;
        return -1
    }

    function n() {
        var t = e(F);
        t.appendTo("body");
        var s = {
            width: t.width() - t[0].clientWidth,
            height: t.height() - t[0].clientHeight
        };
        return t.remove(), s
    }

    function o(e, s) {
        return e === s ? !0 : e === t || s === t ? !1 : null === e || null === s ? !1 : e.constructor === String ? e + "" == s + "" : s.constructor === String ? s + "" == e + "" : !1
    }

    function a(t, s) {
        var i, n, o;
        if (null === t || t.length < 1) return [];
        for (i = t.split(s), n = 0, o = i.length; o > n; n += 1) i[n] = e.trim(i[n]);
        return i
    }

    function r(e) {
        return e.outerWidth(!1) - e.width()
    }

    function c(s) {
        var i = "keyup-change-value";
        s.on("keydown", function() {
            e.data(s, i) === t && e.data(s, i, s.val())
        }), s.on("keyup", function() {
            var n = e.data(s, i);
            n !== t && s.val() !== n && (e.removeData(s, i), s.trigger("keyup-change"))
        })
    }

    function l(s) {
        s.on("mousemove", function(s) {
            var i = M;
            (i === t || i.x !== s.pageX || i.y !== s.pageY) && e(s.target).trigger("mousemove-filtered", s)
        })
    }

    function h(e, s, i) {
        i = i || t;
        var n;
        return function() {
            var t = arguments;
            window.clearTimeout(n), n = window.setTimeout(function() {
                s.apply(i, t)
            }, e)
        }
    }

    function u(e) {
        var t, s = !1;
        return function() {
            return s === !1 && (t = e(), s = !0), t
        }
    }

    function d(e, t) {
        var s = h(e, function(e) {
            t.trigger("scroll-debounced", e)
        });
        t.on("scroll", function(e) {
            i(e.target, t.get()) >= 0 && s(e)
        })
    }

    function p(e) {
        e[0] !== document.activeElement && window.setTimeout(function() {
            var t, s = e[0],
                i = e.val().length;
            e.focus(), e.is(":visible") && s === document.activeElement && (s.setSelectionRange ? s.setSelectionRange(i, i) : s.createTextRange && (t = s.createTextRange(), t.collapse(!1), t.select()))
        }, 0)
    }

    function f(t) {
        t = e(t)[0];
        var s = 0,
            i = 0;
        if ("selectionStart" in t) s = t.selectionStart, i = t.selectionEnd - s;
        else if ("selection" in document) {
            t.focus();
            var n = document.selection.createRange();
            i = document.selection.createRange().text.length, n.moveStart("character", -t.value.length), s = n.text.length - i
        }
        return {
            offset: s,
            length: i
        }
    }

    function g(e) {
        e.preventDefault(), e.stopPropagation()
    }

    function m(e) {
        e.preventDefault(), e.stopImmediatePropagation()
    }

    function v(t) {
        if (!H) {
            var s = t[0].currentStyle || window.getComputedStyle(t[0], null);
            H = e(document.createElement("div")).css({
                position: "absolute",
                left: "-10000px",
                top: "-10000px",
                display: "none",
                fontSize: s.fontSize,
                fontFamily: s.fontFamily,
                fontStyle: s.fontStyle,
                fontWeight: s.fontWeight,
                letterSpacing: s.letterSpacing,
                textTransform: s.textTransform,
                whiteSpace: "nowrap"
            }), H.attr("class", "select2-sizer"), e("body").append(H)
        }
        return H.text(t.val()), H.width()
    }

    function w(t, s, i) {
        var n, o, a = [];
        n = t.attr("class"), n && (n = "" + n, e(n.split(" ")).each2(function() {
            0 === this.indexOf("select2-") && a.push(this)
        })), n = s.attr("class"), n && (n = "" + n, e(n.split(" ")).each2(function() {
            0 !== this.indexOf("select2-") && (o = i(this), o && a.push(o))
        })), t.attr("class", a.join(" "))
    }

    function C(e, t, i, n) {
        var o = s(e.toUpperCase()).indexOf(s(t.toUpperCase())),
            a = t.length;
        return 0 > o ? void i.push(n(e)) : (i.push(n(e.substring(0, o))), i.push("<span class='select2-match'>"), i.push(n(e.substring(o, o + a))), i.push("</span>"), void i.push(n(e.substring(o + a, e.length))))
    }

    function b(e) {
        var t = {
            "\\": "&#92;",
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;",
            "/": "&#47;"
        };
        return String(e).replace(/[&<>"'\/\\]/g, function(e) {
            return t[e]
        })
    }

    function S(s) {
        var i, n = null,
            o = s.quietMillis || 100,
            a = s.url,
            r = this;
        return function(c) {
            window.clearTimeout(i), i = window.setTimeout(function() {
                var i = s.data,
                    o = a,
                    l = s.transport || e.fn.select2.ajaxDefaults.transport,
                    h = {
                        type: s.type || "GET",
                        cache: s.cache || !1,
                        jsonpCallback: s.jsonpCallback || t,
                        dataType: s.dataType || "json"
                    },
                    u = e.extend({}, e.fn.select2.ajaxDefaults.params, h);
                i = i ? i.call(r, c.term, c.page, c.context) : null, o = "function" == typeof o ? o.call(r, c.term, c.page, c.context) : o, n && n.abort(), s.params && (e.isFunction(s.params) ? e.extend(u, s.params.call(r)) : e.extend(u, s.params)), e.extend(u, {
                    url: o,
                    dataType: s.dataType,
                    data: i,
                    success: function(e) {
                        var t = s.results(e, c.page);
                        c.callback(t)
                    }
                }), n = l.call(r, u)
            }, o)
        }
    }

    function y(t) {
        var s, i, n = t,
            o = function(e) {
                return "" + e.text
            };
        e.isArray(n) && (i = n, n = {
            results: i
        }), e.isFunction(n) === !1 && (i = n, n = function() {
            return i
        });
        var a = n();
        return a.text && (o = a.text, e.isFunction(o) || (s = a.text, o = function(e) {
                return e[s]
            })),
            function(t) {
                var s, i = t.term,
                    a = {
                        results: []
                    };
                return "" === i ? void t.callback(n()) : (s = function(n, a) {
                    var r, c;
                    if (n = n[0], n.children) {
                        r = {};
                        for (c in n) n.hasOwnProperty(c) && (r[c] = n[c]);
                        r.children = [], e(n.children).each2(function(e, t) {
                            s(t, r.children)
                        }), (r.children.length || t.matcher(i, o(r), n)) && a.push(r)
                    } else t.matcher(i, o(n), n) && a.push(n)
                }, e(n().results).each2(function(e, t) {
                    s(t, a.results)
                }), void t.callback(a))
            }
    }

    function E(s) {
        var i = e.isFunction(s);
        return function(n) {
            var o = n.term,
                a = {
                    results: []
                };
            e(i ? s() : s).each(function() {
                var e = this.text !== t,
                    s = e ? this.text : this;
                ("" === o || n.matcher(o, s)) && a.results.push(e ? this : {
                    id: this,
                    text: this
                })
            }), n.callback(a)
        }
    }

    function x(t, s) {
        if (e.isFunction(t)) return !0;
        if (!t) return !1;
        throw new Error(s + " must be a function or a falsy value")
    }

    function T(t) {
        return e.isFunction(t) ? t() : t
    }

    function O(t) {
        var s = 0;
        return e.each(t, function(e, t) {
            t.children ? s += O(t.children) : s++
        }), s
    }

    function k(e, s, i, n) {
        var a, r, c, l, h, u = e,
            d = !1;
        if (!n.createSearchChoice || !n.tokenSeparators || n.tokenSeparators.length < 1) return t;
        for (;;) {
            for (r = -1, c = 0, l = n.tokenSeparators.length; l > c && (h = n.tokenSeparators[c], r = e.indexOf(h), !(r >= 0)); c++);
            if (0 > r) break;
            if (a = e.substring(0, r), e = e.substring(r + h.length), a.length > 0 && (a = n.createSearchChoice.call(this, a, s), a !== t && null !== a && n.id(a) !== t && null !== n.id(a))) {
                for (d = !1, c = 0, l = s.length; l > c; c++)
                    if (o(n.id(a), n.id(s[c]))) {
                        d = !0;
                        break
                    }
                d || i(a)
            }
        }
        return u !== e ? e : void 0
    }

    function I(t, s) {
        var i = function() {};
        return i.prototype = new t, i.prototype.constructor = i, i.prototype.parent = t.prototype, i.prototype = e.extend(i.prototype, s), i
    }
    if (window.Select2 === t) {
        var P, A, R, D, L, H, U, N, M = {
                x: 0,
                y: 0
            },
            P = {
                TAB: 9,
                ENTER: 13,
                ESC: 27,
                SPACE: 32,
                LEFT: 37,
                UP: 38,
                RIGHT: 39,
                DOWN: 40,
                SHIFT: 16,
                CTRL: 17,
                ALT: 18,
                PAGE_UP: 33,
                PAGE_DOWN: 34,
                HOME: 36,
                END: 35,
                BACKSPACE: 8,
                DELETE: 46,
                isArrow: function(e) {
                    switch (e = e.which ? e.which : e) {
                        case P.LEFT:
                        case P.RIGHT:
                        case P.UP:
                        case P.DOWN:
                            return !0
                    }
                    return !1
                },
                isControl: function(e) {
                    var t = e.which;
                    switch (t) {
                        case P.SHIFT:
                        case P.CTRL:
                        case P.ALT:
                            return !0
                    }
                    return e.metaKey ? !0 : !1
                },
                isFunctionKey: function(e) {
                    return e = e.which ? e.which : e, e >= 112 && 123 >= e
                }
            },
            F = "<div class='select2-measure-scrollbar'></div>",
            z = {
                "\u24b6": "A",
                "\uff21": "A",
                "\xc0": "A",
                "\xc1": "A",
                "\xc2": "A",
                "\u1ea6": "A",
                "\u1ea4": "A",
                "\u1eaa": "A",
                "\u1ea8": "A",
                "\xc3": "A",
                "\u0100": "A",
                "\u0102": "A",
                "\u1eb0": "A",
                "\u1eae": "A",
                "\u1eb4": "A",
                "\u1eb2": "A",
                "\u0226": "A",
                "\u01e0": "A",
                "\xc4": "A",
                "\u01de": "A",
                "\u1ea2": "A",
                "\xc5": "A",
                "\u01fa": "A",
                "\u01cd": "A",
                "\u0200": "A",
                "\u0202": "A",
                "\u1ea0": "A",
                "\u1eac": "A",
                "\u1eb6": "A",
                "\u1e00": "A",
                "\u0104": "A",
                "\u023a": "A",
                "\u2c6f": "A",
                "\ua732": "AA",
                "\xc6": "AE",
                "\u01fc": "AE",
                "\u01e2": "AE",
                "\ua734": "AO",
                "\ua736": "AU",
                "\ua738": "AV",
                "\ua73a": "AV",
                "\ua73c": "AY",
                "\u24b7": "B",
                "\uff22": "B",
                "\u1e02": "B",
                "\u1e04": "B",
                "\u1e06": "B",
                "\u0243": "B",
                "\u0182": "B",
                "\u0181": "B",
                "\u24b8": "C",
                "\uff23": "C",
                "\u0106": "C",
                "\u0108": "C",
                "\u010a": "C",
                "\u010c": "C",
                "\xc7": "C",
                "\u1e08": "C",
                "\u0187": "C",
                "\u023b": "C",
                "\ua73e": "C",
                "\u24b9": "D",
                "\uff24": "D",
                "\u1e0a": "D",
                "\u010e": "D",
                "\u1e0c": "D",
                "\u1e10": "D",
                "\u1e12": "D",
                "\u1e0e": "D",
                "\u0110": "D",
                "\u018b": "D",
                "\u018a": "D",
                "\u0189": "D",
                "\ua779": "D",
                "\u01f1": "DZ",
                "\u01c4": "DZ",
                "\u01f2": "Dz",
                "\u01c5": "Dz",
                "\u24ba": "E",
                "\uff25": "E",
                "\xc8": "E",
                "\xc9": "E",
                "\xca": "E",
                "\u1ec0": "E",
                "\u1ebe": "E",
                "\u1ec4": "E",
                "\u1ec2": "E",
                "\u1ebc": "E",
                "\u0112": "E",
                "\u1e14": "E",
                "\u1e16": "E",
                "\u0114": "E",
                "\u0116": "E",
                "\xcb": "E",
                "\u1eba": "E",
                "\u011a": "E",
                "\u0204": "E",
                "\u0206": "E",
                "\u1eb8": "E",
                "\u1ec6": "E",
                "\u0228": "E",
                "\u1e1c": "E",
                "\u0118": "E",
                "\u1e18": "E",
                "\u1e1a": "E",
                "\u0190": "E",
                "\u018e": "E",
                "\u24bb": "F",
                "\uff26": "F",
                "\u1e1e": "F",
                "\u0191": "F",
                "\ua77b": "F",
                "\u24bc": "G",
                "\uff27": "G",
                "\u01f4": "G",
                "\u011c": "G",
                "\u1e20": "G",
                "\u011e": "G",
                "\u0120": "G",
                "\u01e6": "G",
                "\u0122": "G",
                "\u01e4": "G",
                "\u0193": "G",
                "\ua7a0": "G",
                "\ua77d": "G",
                "\ua77e": "G",
                "\u24bd": "H",
                "\uff28": "H",
                "\u0124": "H",
                "\u1e22": "H",
                "\u1e26": "H",
                "\u021e": "H",
                "\u1e24": "H",
                "\u1e28": "H",
                "\u1e2a": "H",
                "\u0126": "H",
                "\u2c67": "H",
                "\u2c75": "H",
                "\ua78d": "H",
                "\u24be": "I",
                "\uff29": "I",
                "\xcc": "I",
                "\xcd": "I",
                "\xce": "I",
                "\u0128": "I",
                "\u012a": "I",
                "\u012c": "I",
                "\u0130": "I",
                "\xcf": "I",
                "\u1e2e": "I",
                "\u1ec8": "I",
                "\u01cf": "I",
                "\u0208": "I",
                "\u020a": "I",
                "\u1eca": "I",
                "\u012e": "I",
                "\u1e2c": "I",
                "\u0197": "I",
                "\u24bf": "J",
                "\uff2a": "J",
                "\u0134": "J",
                "\u0248": "J",
                "\u24c0": "K",
                "\uff2b": "K",
                "\u1e30": "K",
                "\u01e8": "K",
                "\u1e32": "K",
                "\u0136": "K",
                "\u1e34": "K",
                "\u0198": "K",
                "\u2c69": "K",
                "\ua740": "K",
                "\ua742": "K",
                "\ua744": "K",
                "\ua7a2": "K",
                "\u24c1": "L",
                "\uff2c": "L",
                "\u013f": "L",
                "\u0139": "L",
                "\u013d": "L",
                "\u1e36": "L",
                "\u1e38": "L",
                "\u013b": "L",
                "\u1e3c": "L",
                "\u1e3a": "L",
                "\u0141": "L",
                "\u023d": "L",
                "\u2c62": "L",
                "\u2c60": "L",
                "\ua748": "L",
                "\ua746": "L",
                "\ua780": "L",
                "\u01c7": "LJ",
                "\u01c8": "Lj",
                "\u24c2": "M",
                "\uff2d": "M",
                "\u1e3e": "M",
                "\u1e40": "M",
                "\u1e42": "M",
                "\u2c6e": "M",
                "\u019c": "M",
                "\u24c3": "N",
                "\uff2e": "N",
                "\u01f8": "N",
                "\u0143": "N",
                "\xd1": "N",
                "\u1e44": "N",
                "\u0147": "N",
                "\u1e46": "N",
                "\u0145": "N",
                "\u1e4a": "N",
                "\u1e48": "N",
                "\u0220": "N",
                "\u019d": "N",
                "\ua790": "N",
                "\ua7a4": "N",
                "\u01ca": "NJ",
                "\u01cb": "Nj",
                "\u24c4": "O",
                "\uff2f": "O",
                "\xd2": "O",
                "\xd3": "O",
                "\xd4": "O",
                "\u1ed2": "O",
                "\u1ed0": "O",
                "\u1ed6": "O",
                "\u1ed4": "O",
                "\xd5": "O",
                "\u1e4c": "O",
                "\u022c": "O",
                "\u1e4e": "O",
                "\u014c": "O",
                "\u1e50": "O",
                "\u1e52": "O",
                "\u014e": "O",
                "\u022e": "O",
                "\u0230": "O",
                "\xd6": "O",
                "\u022a": "O",
                "\u1ece": "O",
                "\u0150": "O",
                "\u01d1": "O",
                "\u020c": "O",
                "\u020e": "O",
                "\u01a0": "O",
                "\u1edc": "O",
                "\u1eda": "O",
                "\u1ee0": "O",
                "\u1ede": "O",
                "\u1ee2": "O",
                "\u1ecc": "O",
                "\u1ed8": "O",
                "\u01ea": "O",
                "\u01ec": "O",
                "\xd8": "O",
                "\u01fe": "O",
                "\u0186": "O",
                "\u019f": "O",
                "\ua74a": "O",
                "\ua74c": "O",
                "\u01a2": "OI",
                "\ua74e": "OO",
                "\u0222": "OU",
                "\u24c5": "P",
                "\uff30": "P",
                "\u1e54": "P",
                "\u1e56": "P",
                "\u01a4": "P",
                "\u2c63": "P",
                "\ua750": "P",
                "\ua752": "P",
                "\ua754": "P",
                "\u24c6": "Q",
                "\uff31": "Q",
                "\ua756": "Q",
                "\ua758": "Q",
                "\u024a": "Q",
                "\u24c7": "R",
                "\uff32": "R",
                "\u0154": "R",
                "\u1e58": "R",
                "\u0158": "R",
                "\u0210": "R",
                "\u0212": "R",
                "\u1e5a": "R",
                "\u1e5c": "R",
                "\u0156": "R",
                "\u1e5e": "R",
                "\u024c": "R",
                "\u2c64": "R",
                "\ua75a": "R",
                "\ua7a6": "R",
                "\ua782": "R",
                "\u24c8": "S",
                "\uff33": "S",
                "\u1e9e": "S",
                "\u015a": "S",
                "\u1e64": "S",
                "\u015c": "S",
                "\u1e60": "S",
                "\u0160": "S",
                "\u1e66": "S",
                "\u1e62": "S",
                "\u1e68": "S",
                "\u0218": "S",
                "\u015e": "S",
                "\u2c7e": "S",
                "\ua7a8": "S",
                "\ua784": "S",
                "\u24c9": "T",
                "\uff34": "T",
                "\u1e6a": "T",
                "\u0164": "T",
                "\u1e6c": "T",
                "\u021a": "T",
                "\u0162": "T",
                "\u1e70": "T",
                "\u1e6e": "T",
                "\u0166": "T",
                "\u01ac": "T",
                "\u01ae": "T",
                "\u023e": "T",
                "\ua786": "T",
                "\ua728": "TZ",
                "\u24ca": "U",
                "\uff35": "U",
                "\xd9": "U",
                "\xda": "U",
                "\xdb": "U",
                "\u0168": "U",
                "\u1e78": "U",
                "\u016a": "U",
                "\u1e7a": "U",
                "\u016c": "U",
                "\xdc": "U",
                "\u01db": "U",
                "\u01d7": "U",
                "\u01d5": "U",
                "\u01d9": "U",
                "\u1ee6": "U",
                "\u016e": "U",
                "\u0170": "U",
                "\u01d3": "U",
                "\u0214": "U",
                "\u0216": "U",
                "\u01af": "U",
                "\u1eea": "U",
                "\u1ee8": "U",
                "\u1eee": "U",
                "\u1eec": "U",
                "\u1ef0": "U",
                "\u1ee4": "U",
                "\u1e72": "U",
                "\u0172": "U",
                "\u1e76": "U",
                "\u1e74": "U",
                "\u0244": "U",
                "\u24cb": "V",
                "\uff36": "V",
                "\u1e7c": "V",
                "\u1e7e": "V",
                "\u01b2": "V",
                "\ua75e": "V",
                "\u0245": "V",
                "\ua760": "VY",
                "\u24cc": "W",
                "\uff37": "W",
                "\u1e80": "W",
                "\u1e82": "W",
                "\u0174": "W",
                "\u1e86": "W",
                "\u1e84": "W",
                "\u1e88": "W",
                "\u2c72": "W",
                "\u24cd": "X",
                "\uff38": "X",
                "\u1e8a": "X",
                "\u1e8c": "X",
                "\u24ce": "Y",
                "\uff39": "Y",
                "\u1ef2": "Y",
                "\xdd": "Y",
                "\u0176": "Y",
                "\u1ef8": "Y",
                "\u0232": "Y",
                "\u1e8e": "Y",
                "\u0178": "Y",
                "\u1ef6": "Y",
                "\u1ef4": "Y",
                "\u01b3": "Y",
                "\u024e": "Y",
                "\u1efe": "Y",
                "\u24cf": "Z",
                "\uff3a": "Z",
                "\u0179": "Z",
                "\u1e90": "Z",
                "\u017b": "Z",
                "\u017d": "Z",
                "\u1e92": "Z",
                "\u1e94": "Z",
                "\u01b5": "Z",
                "\u0224": "Z",
                "\u2c7f": "Z",
                "\u2c6b": "Z",
                "\ua762": "Z",
                "\u24d0": "a",
                "\uff41": "a",
                "\u1e9a": "a",
                "\xe0": "a",
                "\xe1": "a",
                "\xe2": "a",
                "\u1ea7": "a",
                "\u1ea5": "a",
                "\u1eab": "a",
                "\u1ea9": "a",
                "\xe3": "a",
                "\u0101": "a",
                "\u0103": "a",
                "\u1eb1": "a",
                "\u1eaf": "a",
                "\u1eb5": "a",
                "\u1eb3": "a",
                "\u0227": "a",
                "\u01e1": "a",
                "\xe4": "a",
                "\u01df": "a",
                "\u1ea3": "a",
                "\xe5": "a",
                "\u01fb": "a",
                "\u01ce": "a",
                "\u0201": "a",
                "\u0203": "a",
                "\u1ea1": "a",
                "\u1ead": "a",
                "\u1eb7": "a",
                "\u1e01": "a",
                "\u0105": "a",
                "\u2c65": "a",
                "\u0250": "a",
                "\ua733": "aa",
                "\xe6": "ae",
                "\u01fd": "ae",
                "\u01e3": "ae",
                "\ua735": "ao",
                "\ua737": "au",
                "\ua739": "av",
                "\ua73b": "av",
                "\ua73d": "ay",
                "\u24d1": "b",
                "\uff42": "b",
                "\u1e03": "b",
                "\u1e05": "b",
                "\u1e07": "b",
                "\u0180": "b",
                "\u0183": "b",
                "\u0253": "b",
                "\u24d2": "c",
                "\uff43": "c",
                "\u0107": "c",
                "\u0109": "c",
                "\u010b": "c",
                "\u010d": "c",
                "\xe7": "c",
                "\u1e09": "c",
                "\u0188": "c",
                "\u023c": "c",
                "\ua73f": "c",
                "\u2184": "c",
                "\u24d3": "d",
                "\uff44": "d",
                "\u1e0b": "d",
                "\u010f": "d",
                "\u1e0d": "d",
                "\u1e11": "d",
                "\u1e13": "d",
                "\u1e0f": "d",
                "\u0111": "d",
                "\u018c": "d",
                "\u0256": "d",
                "\u0257": "d",
                "\ua77a": "d",
                "\u01f3": "dz",
                "\u01c6": "dz",
                "\u24d4": "e",
                "\uff45": "e",
                "\xe8": "e",
                "\xe9": "e",
                "\xea": "e",
                "\u1ec1": "e",
                "\u1ebf": "e",
                "\u1ec5": "e",
                "\u1ec3": "e",
                "\u1ebd": "e",
                "\u0113": "e",
                "\u1e15": "e",
                "\u1e17": "e",
                "\u0115": "e",
                "\u0117": "e",
                "\xeb": "e",
                "\u1ebb": "e",
                "\u011b": "e",
                "\u0205": "e",
                "\u0207": "e",
                "\u1eb9": "e",
                "\u1ec7": "e",
                "\u0229": "e",
                "\u1e1d": "e",
                "\u0119": "e",
                "\u1e19": "e",
                "\u1e1b": "e",
                "\u0247": "e",
                "\u025b": "e",
                "\u01dd": "e",
                "\u24d5": "f",
                "\uff46": "f",
                "\u1e1f": "f",
                "\u0192": "f",
                "\ua77c": "f",
                "\u24d6": "g",
                "\uff47": "g",
                "\u01f5": "g",
                "\u011d": "g",
                "\u1e21": "g",
                "\u011f": "g",
                "\u0121": "g",
                "\u01e7": "g",
                "\u0123": "g",
                "\u01e5": "g",
                "\u0260": "g",
                "\ua7a1": "g",
                "\u1d79": "g",
                "\ua77f": "g",
                "\u24d7": "h",
                "\uff48": "h",
                "\u0125": "h",
                "\u1e23": "h",
                "\u1e27": "h",
                "\u021f": "h",
                "\u1e25": "h",
                "\u1e29": "h",
                "\u1e2b": "h",
                "\u1e96": "h",
                "\u0127": "h",
                "\u2c68": "h",
                "\u2c76": "h",
                "\u0265": "h",
                "\u0195": "hv",
                "\u24d8": "i",
                "\uff49": "i",
                "\xec": "i",
                "\xed": "i",
                "\xee": "i",
                "\u0129": "i",
                "\u012b": "i",
                "\u012d": "i",
                "\xef": "i",
                "\u1e2f": "i",
                "\u1ec9": "i",
                "\u01d0": "i",
                "\u0209": "i",
                "\u020b": "i",
                "\u1ecb": "i",
                "\u012f": "i",
                "\u1e2d": "i",
                "\u0268": "i",
                "\u0131": "i",
                "\u24d9": "j",
                "\uff4a": "j",
                "\u0135": "j",
                "\u01f0": "j",
                "\u0249": "j",
                "\u24da": "k",
                "\uff4b": "k",
                "\u1e31": "k",
                "\u01e9": "k",
                "\u1e33": "k",
                "\u0137": "k",
                "\u1e35": "k",
                "\u0199": "k",
                "\u2c6a": "k",
                "\ua741": "k",
                "\ua743": "k",
                "\ua745": "k",
                "\ua7a3": "k",
                "\u24db": "l",
                "\uff4c": "l",
                "\u0140": "l",
                "\u013a": "l",
                "\u013e": "l",
                "\u1e37": "l",
                "\u1e39": "l",
                "\u013c": "l",
                "\u1e3d": "l",
                "\u1e3b": "l",
                "\u017f": "l",
                "\u0142": "l",
                "\u019a": "l",
                "\u026b": "l",
                "\u2c61": "l",
                "\ua749": "l",
                "\ua781": "l",
                "\ua747": "l",
                "\u01c9": "lj",
                "\u24dc": "m",
                "\uff4d": "m",
                "\u1e3f": "m",
                "\u1e41": "m",
                "\u1e43": "m",
                "\u0271": "m",
                "\u026f": "m",
                "\u24dd": "n",
                "\uff4e": "n",
                "\u01f9": "n",
                "\u0144": "n",
                "\xf1": "n",
                "\u1e45": "n",
                "\u0148": "n",
                "\u1e47": "n",
                "\u0146": "n",
                "\u1e4b": "n",
                "\u1e49": "n",
                "\u019e": "n",
                "\u0272": "n",
                "\u0149": "n",
                "\ua791": "n",
                "\ua7a5": "n",
                "\u01cc": "nj",
                "\u24de": "o",
                "\uff4f": "o",
                "\xf2": "o",
                "\xf3": "o",
                "\xf4": "o",
                "\u1ed3": "o",
                "\u1ed1": "o",
                "\u1ed7": "o",
                "\u1ed5": "o",
                "\xf5": "o",
                "\u1e4d": "o",
                "\u022d": "o",
                "\u1e4f": "o",
                "\u014d": "o",
                "\u1e51": "o",
                "\u1e53": "o",
                "\u014f": "o",
                "\u022f": "o",
                "\u0231": "o",
                "\xf6": "o",
                "\u022b": "o",
                "\u1ecf": "o",
                "\u0151": "o",
                "\u01d2": "o",
                "\u020d": "o",
                "\u020f": "o",
                "\u01a1": "o",
                "\u1edd": "o",
                "\u1edb": "o",
                "\u1ee1": "o",
                "\u1edf": "o",
                "\u1ee3": "o",
                "\u1ecd": "o",
                "\u1ed9": "o",
                "\u01eb": "o",
                "\u01ed": "o",
                "\xf8": "o",
                "\u01ff": "o",
                "\u0254": "o",
                "\ua74b": "o",
                "\ua74d": "o",
                "\u0275": "o",
                "\u01a3": "oi",
                "\u0223": "ou",
                "\ua74f": "oo",
                "\u24df": "p",
                "\uff50": "p",
                "\u1e55": "p",
                "\u1e57": "p",
                "\u01a5": "p",
                "\u1d7d": "p",
                "\ua751": "p",
                "\ua753": "p",
                "\ua755": "p",
                "\u24e0": "q",
                "\uff51": "q",
                "\u024b": "q",
                "\ua757": "q",
                "\ua759": "q",
                "\u24e1": "r",
                "\uff52": "r",
                "\u0155": "r",
                "\u1e59": "r",
                "\u0159": "r",
                "\u0211": "r",
                "\u0213": "r",
                "\u1e5b": "r",
                "\u1e5d": "r",
                "\u0157": "r",
                "\u1e5f": "r",
                "\u024d": "r",
                "\u027d": "r",
                "\ua75b": "r",
                "\ua7a7": "r",
                "\ua783": "r",
                "\u24e2": "s",
                "\uff53": "s",
                "\xdf": "s",
                "\u015b": "s",
                "\u1e65": "s",
                "\u015d": "s",
                "\u1e61": "s",
                "\u0161": "s",
                "\u1e67": "s",
                "\u1e63": "s",
                "\u1e69": "s",
                "\u0219": "s",
                "\u015f": "s",
                "\u023f": "s",
                "\ua7a9": "s",
                "\ua785": "s",
                "\u1e9b": "s",
                "\u24e3": "t",
                "\uff54": "t",
                "\u1e6b": "t",
                "\u1e97": "t",
                "\u0165": "t",
                "\u1e6d": "t",
                "\u021b": "t",
                "\u0163": "t",
                "\u1e71": "t",
                "\u1e6f": "t",
                "\u0167": "t",
                "\u01ad": "t",
                "\u0288": "t",
                "\u2c66": "t",
                "\ua787": "t",
                "\ua729": "tz",
                "\u24e4": "u",
                "\uff55": "u",
                "\xf9": "u",
                "\xfa": "u",
                "\xfb": "u",
                "\u0169": "u",
                "\u1e79": "u",
                "\u016b": "u",
                "\u1e7b": "u",
                "\u016d": "u",
                "\xfc": "u",
                "\u01dc": "u",
                "\u01d8": "u",
                "\u01d6": "u",
                "\u01da": "u",
                "\u1ee7": "u",
                "\u016f": "u",
                "\u0171": "u",
                "\u01d4": "u",
                "\u0215": "u",
                "\u0217": "u",
                "\u01b0": "u",
                "\u1eeb": "u",
                "\u1ee9": "u",
                "\u1eef": "u",
                "\u1eed": "u",
                "\u1ef1": "u",
                "\u1ee5": "u",
                "\u1e73": "u",
                "\u0173": "u",
                "\u1e77": "u",
                "\u1e75": "u",
                "\u0289": "u",
                "\u24e5": "v",
                "\uff56": "v",
                "\u1e7d": "v",
                "\u1e7f": "v",
                "\u028b": "v",
                "\ua75f": "v",
                "\u028c": "v",
                "\ua761": "vy",
                "\u24e6": "w",
                "\uff57": "w",
                "\u1e81": "w",
                "\u1e83": "w",
                "\u0175": "w",
                "\u1e87": "w",
                "\u1e85": "w",
                "\u1e98": "w",
                "\u1e89": "w",
                "\u2c73": "w",
                "\u24e7": "x",
                "\uff58": "x",
                "\u1e8b": "x",
                "\u1e8d": "x",
                "\u24e8": "y",
                "\uff59": "y",
                "\u1ef3": "y",
                "\xfd": "y",
                "\u0177": "y",
                "\u1ef9": "y",
                "\u0233": "y",
                "\u1e8f": "y",
                "\xff": "y",
                "\u1ef7": "y",
                "\u1e99": "y",
                "\u1ef5": "y",
                "\u01b4": "y",
                "\u024f": "y",
                "\u1eff": "y",
                "\u24e9": "z",
                "\uff5a": "z",
                "\u017a": "z",
                "\u1e91": "z",
                "\u017c": "z",
                "\u017e": "z",
                "\u1e93": "z",
                "\u1e95": "z",
                "\u01b6": "z",
                "\u0225": "z",
                "\u0240": "z",
                "\u2c6c": "z",
                "\ua763": "z"
            };
        U = e(document), L = function() {
            var e = 1;
            return function() {
                return e++
            }
        }(), U.on("mousemove", function(e) {
            M.x = e.pageX, M.y = e.pageY
        }), A = I(Object, {
            bind: function(e) {
                var t = this;
                return function() {
                    e.apply(t, arguments)
                }
            },
            init: function(s) {
                var i, o, a = ".select2-results";
                this.opts = s = this.prepareOpts(s), this.id = s.id, s.element.data("select2") !== t && null !== s.element.data("select2") && s.element.data("select2").destroy(), this.container = this.createContainer(), this.containerId = "s2id_" + (s.element.attr("id") || "autogen" + L()), this.containerSelector = "#" + this.containerId.replace(/([;&,\.\+\*\~':"\!\^#$%@\[\]\(\)=>\|])/g, "\\$1"), this.container.attr("id", this.containerId), this.body = u(function() {
                    return s.element.closest("body")
                }), w(this.container, this.opts.element, this.opts.adaptContainerCssClass), this.container.attr("style", s.element.attr("style")), this.container.css(T(s.containerCss)), this.container.addClass(T(s.containerCssClass)), this.elementTabIndex = this.opts.element.attr("tabindex"), this.opts.element.data("select2", this).attr("tabindex", "-1").before(this.container).on("click.select2", g), this.container.data("select2", this), this.dropdown = this.container.find(".select2-drop"), w(this.dropdown, this.opts.element, this.opts.adaptDropdownCssClass), this.dropdown.addClass(T(s.dropdownCssClass)), this.dropdown.data("select2", this), this.dropdown.on("click", g), this.results = i = this.container.find(a), this.search = o = this.container.find("input.select2-input"), this.queryCount = 0, this.resultsPage = 0, this.context = null, this.initContainer(), this.container.on("click", g), l(this.results), this.dropdown.on("mousemove-filtered touchstart touchmove touchend", a, this.bind(this.highlightUnderEvent)), d(80, this.results), this.dropdown.on("scroll-debounced", a, this.bind(this.loadMoreIfNeeded)), e(this.container).on("change", ".select2-input", function(e) {
                    e.stopPropagation()
                }), e(this.dropdown).on("change", ".select2-input", function(e) {
                    e.stopPropagation()
                }), e.fn.mousewheel && i.mousewheel(function(e, t, s, n) {
                    var o = i.scrollTop();
                    n > 0 && 0 >= o - n ? (i.scrollTop(0), g(e)) : 0 > n && i.get(0).scrollHeight - i.scrollTop() + n <= i.height() && (i.scrollTop(i.get(0).scrollHeight - i.height()), g(e))
                }), c(o), o.on("keyup-change input paste", this.bind(this.updateResults)), o.on("focus", function() {
                    o.addClass("select2-focused")
                }), o.on("blur", function() {
                    o.removeClass("select2-focused")
                }), this.dropdown.on("mouseup", a, this.bind(function(t) {
                    e(t.target).closest(".select2-result-selectable").length > 0 && (this.highlightUnderEvent(t), this.selectHighlighted(t))
                })), this.dropdown.on("click mouseup mousedown", function(e) {
                    e.stopPropagation()
                }), e.isFunction(this.opts.initSelection) && (this.initSelection(), this.monitorSource()), null !== s.maximumInputLength && this.search.attr("maxlength", s.maximumInputLength);
                var r = s.element.prop("disabled");
                r === t && (r = !1), this.enable(!r);
                var h = s.element.prop("readonly");
                h === t && (h = !1), this.readonly(h), N = N || n(), this.autofocus = s.element.prop("autofocus"), s.element.prop("autofocus", !1), this.autofocus && this.focus(), this.nextSearchTerm = t
            },
            destroy: function() {
                var e = this.opts.element,
                    s = e.data("select2");
                this.close(), this.propertyObserver && (delete this.propertyObserver, this.propertyObserver = null), s !== t && (s.container.remove(), s.dropdown.remove(), e.removeClass("select2-offscreen").removeData("select2").off(".select2").prop("autofocus", this.autofocus || !1), this.elementTabIndex ? e.attr({
                    tabindex: this.elementTabIndex
                }) : e.removeAttr("tabindex"), e.show())
            },
            optionToData: function(e) {
                return e.is("option") ? {
                    id: e.prop("value"),
                    text: e.text(),
                    element: e.get(),
                    css: e.attr("class"),
                    disabled: e.prop("disabled"),
                    locked: o(e.attr("locked"), "locked") || o(e.data("locked"), !0)
                } : e.is("optgroup") ? {
                    text: e.attr("label"),
                    children: [],
                    element: e.get(),
                    css: e.attr("class")
                } : void 0
            },
            prepareOpts: function(s) {
                var i, n, r, c, l = this;
                if (i = s.element, "select" === i.get(0).tagName.toLowerCase() && (this.select = n = s.element), n && e.each(["id", "multiple", "ajax", "query", "createSearchChoice", "initSelection", "data", "tags"], function() {
                        if (this in s) throw new Error("Option '" + this + "' is not allowed for Select2 when attached to a <select> element.")
                    }), s = e.extend({}, {
                        populateResults: function(i, n, o) {
                            var a, r = this.opts.id;
                            (a = function(i, n, c) {
                                var h, u, d, p, f, g, m, v, w, C;
                                for (i = s.sortResults(i, n, o), h = 0, u = i.length; u > h; h += 1) d = i[h], f = d.disabled === !0, p = !f && r(d) !== t, g = d.children && d.children.length > 0, m = e("<li></li>"), m.addClass("select2-results-dept-" + c), m.addClass("select2-result"), m.addClass(p ? "select2-result-selectable" : "select2-result-unselectable"), f && m.addClass("select2-disabled"), g && m.addClass("select2-result-with-children"), m.addClass(l.opts.formatResultCssClass(d)), v = e(document.createElement("div")), v.addClass("select2-result-label"), C = s.formatResult(d, v, o, l.opts.escapeMarkup), C !== t && v.html(C), m.append(v), g && (w = e("<ul></ul>"), w.addClass("select2-result-sub"), a(d.children, w, c + 1), m.append(w)), m.data("select2-data", d), n.append(m)
                            })(n, i, 0)
                        }
                    }, e.fn.select2.defaults, s), "function" != typeof s.id && (r = s.id, s.id = function(e) {
                        return e[r]
                    }), e.isArray(s.element.data("select2Tags"))) {
                    if ("tags" in s) throw "tags specified as both an attribute 'data-select2-tags' and in options of Select2 " + s.element.attr("id");
                    s.tags = s.element.data("select2Tags")
                }
                if (n ? (s.query = this.bind(function(e) {
                        var s, n, o, a = {
                                results: [],
                                more: !1
                            },
                            r = e.term;
                        o = function(t, s) {
                            var i;
                            t.is("option") ? e.matcher(r, t.text(), t) && s.push(l.optionToData(t)) : t.is("optgroup") && (i = l.optionToData(t), t.children().each2(function(e, t) {
                                o(t, i.children)
                            }), i.children.length > 0 && s.push(i))
                        }, s = i.children(), this.getPlaceholder() !== t && s.length > 0 && (n = this.getPlaceholderOption(), n && (s = s.not(n))), s.each2(function(e, t) {
                            o(t, a.results)
                        }), e.callback(a)
                    }), s.id = function(e) {
                        return e.id
                    }, s.formatResultCssClass = function(e) {
                        return e.css
                    }) : "query" in s || ("ajax" in s ? (c = s.element.data("ajax-url"), c && c.length > 0 && (s.ajax.url = c), s.query = S.call(s.element, s.ajax)) : "data" in s ? s.query = y(s.data) : "tags" in s && (s.query = E(s.tags), s.createSearchChoice === t && (s.createSearchChoice = function(t) {
                        return {
                            id: e.trim(t),
                            text: e.trim(t)
                        }
                    }), s.initSelection === t && (s.initSelection = function(t, i) {
                        var n = [];
                        e(a(t.val(), s.separator)).each(function() {
                            var t = {
                                    id: this,
                                    text: this
                                },
                                i = s.tags;
                            e.isFunction(i) && (i = i()), e(i).each(function() {
                                return o(this.id, t.id) ? (t = this, !1) : void 0
                            }), n.push(t)
                        }), i(n)
                    }))), "function" != typeof s.query) throw "query function not defined for Select2 " + s.element.attr("id");
                return s
            },
            monitorSource: function() {
                var e, s, i = this.opts.element;
                i.on("change.select2", this.bind(function() {
                    this.opts.element.data("select2-change-triggered") !== !0 && this.initSelection()
                })), e = this.bind(function() {
                    var e = i.prop("disabled");
                    e === t && (e = !1), this.enable(!e);
                    var s = i.prop("readonly");
                    s === t && (s = !1), this.readonly(s), w(this.container, this.opts.element, this.opts.adaptContainerCssClass), this.container.addClass(T(this.opts.containerCssClass)), w(this.dropdown, this.opts.element, this.opts.adaptDropdownCssClass), this.dropdown.addClass(T(this.opts.dropdownCssClass))
                }), i.on("propertychange.select2", e), this.mutationCallback === t && (this.mutationCallback = function(t) {
                    t.forEach(e)
                }), s = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver, s !== t && (this.propertyObserver && (delete this.propertyObserver, this.propertyObserver = null), this.propertyObserver = new s(this.mutationCallback), this.propertyObserver.observe(i.get(0), {
                    attributes: !0,
                    subtree: !1
                }))
            },
            triggerSelect: function(t) {
                var s = e.Event("select2-selecting", {
                    val: this.id(t),
                    object: t
                });
                return this.opts.element.trigger(s), !s.isDefaultPrevented()
            },
            triggerChange: function(t) {
                t = t || {}, t = e.extend({}, t, {
                    type: "change",
                    val: this.val()
                }), this.opts.element.data("select2-change-triggered", !0), this.opts.element.trigger(t), this.opts.element.data("select2-change-triggered", !1), this.opts.element.click(), this.opts.blurOnChange && this.opts.element.blur()
            },
            isInterfaceEnabled: function() {
                return this.enabledInterface === !0
            },
            enableInterface: function() {
                var e = this._enabled && !this._readonly,
                    t = !e;
                return e === this.enabledInterface ? !1 : (this.container.toggleClass("select2-container-disabled", t), this.close(), this.enabledInterface = e, !0)
            },
            enable: function(e) {
                e === t && (e = !0), this._enabled !== e && (this._enabled = e, this.opts.element.prop("disabled", !e), this.enableInterface())
            },
            disable: function() {
                this.enable(!1)
            },
            readonly: function(e) {
                return e === t && (e = !1), this._readonly === e ? !1 : (this._readonly = e, this.opts.element.prop("readonly", e), this.enableInterface(), !0)
            },
            opened: function() {
                return this.container.hasClass("select2-dropdown-open")
            },
            positionDropdown: function() {
                var t, s, i, n, o, a = this.dropdown,
                    r = this.container.offset(),
                    c = this.container.outerHeight(!1),
                    l = this.container.outerWidth(!1),
                    h = a.outerHeight(!1),
                    u = e(window),
                    d = u.width(),
                    p = u.height(),
                    f = u.scrollLeft() + d,
                    g = u.scrollTop() + p,
                    m = r.top + c,
                    v = r.left,
                    w = g >= m + h,
                    C = r.top - h >= this.body().scrollTop(),
                    b = a.outerWidth(!1),
                    S = f >= v + b,
                    y = a.hasClass("select2-drop-above");
                y ? (s = !0, !C && w && (i = !0, s = !1)) : (s = !1, !w && C && (i = !0, s = !0)), i && (a.hide(), r = this.container.offset(), c = this.container.outerHeight(!1), l = this.container.outerWidth(!1), h = a.outerHeight(!1), f = u.scrollLeft() + d, g = u.scrollTop() + p, m = r.top + c, v = r.left, b = a.outerWidth(!1), S = f >= v + b, a.show()), this.opts.dropdownAutoWidth ? (o = e(".select2-results", a)[0], a.addClass("select2-drop-auto-width"), a.css("width", ""), b = a.outerWidth(!1) + (o.scrollHeight === o.clientHeight ? 0 : N.width), b > l ? l = b : b = l, S = f >= v + b) : this.container.removeClass("select2-drop-auto-width"), "static" !== this.body().css("position") && (t = this.body().offset(), m -= t.top, v -= t.left), S || (v = r.left + l - b), n = {
                    left: v,
                    width: l
                }, s ? (n.bottom = p - r.top, n.top = "auto", this.container.addClass("select2-drop-above"), a.addClass("select2-drop-above")) : (n.top = m, n.bottom = "auto", this.container.removeClass("select2-drop-above"), a.removeClass("select2-drop-above")), n = e.extend(n, T(this.opts.dropdownCss)), a.css(n)
            },
            shouldOpen: function() {
                var t;
                return this.opened() ? !1 : this._enabled === !1 || this._readonly === !0 ? !1 : (t = e.Event("select2-opening"), this.opts.element.trigger(t), !t.isDefaultPrevented())
            },
            clearDropdownAlignmentPreference: function() {
                this.container.removeClass("select2-drop-above"), this.dropdown.removeClass("select2-drop-above")
            },
            open: function() {
                return this.shouldOpen() ? (this.opening(), !0) : !1
            },
            opening: function() {
                var t, s = this.containerId,
                    i = "scroll." + s,
                    n = "resize." + s,
                    o = "orientationchange." + s;
                this.container.addClass("select2-dropdown-open").addClass("select2-container-active"), this.clearDropdownAlignmentPreference(), this.dropdown[0] !== this.body().children().last()[0] && this.dropdown.detach().appendTo(this.body()), t = e("#select2-drop-mask"), 0 == t.length && (t = e(document.createElement("div")), t.attr("id", "select2-drop-mask").attr("class", "select2-drop-mask"), t.hide(), t.appendTo(this.body()), t.on("mousedown touchstart click", function(t) {
                    var s, i = e("#select2-drop");
                    i.length > 0 && (s = i.data("select2"), s.opts.selectOnBlur && s.selectHighlighted({
                        noFocus: !0
                    }), s.close({
                        focus: !0
                    }), t.preventDefault(), t.stopPropagation())
                })), this.dropdown.prev()[0] !== t[0] && this.dropdown.before(t), e("#select2-drop").removeAttr("id"), this.dropdown.attr("id", "select2-drop"), t.show(), this.positionDropdown(), this.dropdown.show(), this.positionDropdown(), this.dropdown.addClass("select2-drop-active");
                var a = this;
                this.container.parents().add(window).each(function() {
                    e(this).on(n + " " + i + " " + o, function() {
                        a.positionDropdown()
                    })
                })
            },
            close: function() {
                if (this.opened()) {
                    var t = this.containerId,
                        s = "scroll." + t,
                        i = "resize." + t,
                        n = "orientationchange." + t;
                    this.container.parents().add(window).each(function() {
                        e(this).off(s).off(i).off(n)
                    }), this.clearDropdownAlignmentPreference(), e("#select2-drop-mask").hide(), this.dropdown.removeAttr("id"), this.dropdown.hide(), this.container.removeClass("select2-dropdown-open").removeClass("select2-container-active"), this.results.empty(), this.clearSearch(), this.search.removeClass("select2-active"), this.opts.element.trigger(e.Event("select2-close"))
                }
            },
            externalSearch: function(e) {
                this.open(), this.search.val(e), this.updateResults(!1)
            },
            clearSearch: function() {},
            getMaximumSelectionSize: function() {
                return T(this.opts.maximumSelectionSize)
            },
            ensureHighlightVisible: function() {
                var t, s, i, n, o, a, r, c = this.results;
                if (s = this.highlight(), !(0 > s)) {
                    if (0 == s) return void c.scrollTop(0);
                    t = this.findHighlightableChoices().find(".select2-result-label"), i = e(t[s]), n = i.offset().top + i.outerHeight(!0), s === t.length - 1 && (r = c.find("li.select2-more-results"), r.length > 0 && (n = r.offset().top + r.outerHeight(!0))), o = c.offset().top + c.outerHeight(!0), n > o && c.scrollTop(c.scrollTop() + (n - o)), a = i.offset().top - c.offset().top, 0 > a && "none" != i.css("display") && c.scrollTop(c.scrollTop() + a)
                }
            },
            findHighlightableChoices: function() {
                return this.results.find(".select2-result-selectable:not(.select2-disabled, .select2-selected)")
            },
            moveHighlight: function(t) {
                for (var s = this.findHighlightableChoices(), i = this.highlight(); i > -1 && i < s.length;) {
                    i += t;
                    var n = e(s[i]);
                    if (n.hasClass("select2-result-selectable") && !n.hasClass("select2-disabled") && !n.hasClass("select2-selected")) {
                        this.highlight(i);
                        break
                    }
                }
            },
            highlight: function(t) {
                var s, n, o = this.findHighlightableChoices();
                return 0 === arguments.length ? i(o.filter(".select2-highlighted")[0], o.get()) : (t >= o.length && (t = o.length - 1), 0 > t && (t = 0), this.removeHighlight(), s = e(o[t]), s.addClass("select2-highlighted"), this.ensureHighlightVisible(), n = s.data("select2-data"), void(n && this.opts.element.trigger({
                    type: "select2-highlight",
                    val: this.id(n),
                    choice: n
                })))
            },
            removeHighlight: function() {
                this.results.find(".select2-highlighted").removeClass("select2-highlighted")
            },
            countSelectableResults: function() {
                return this.findHighlightableChoices().length
            },
            highlightUnderEvent: function(t) {
                var s = e(t.target).closest(".select2-result-selectable");
                if (s.length > 0 && !s.is(".select2-highlighted")) {
                    var i = this.findHighlightableChoices();
                    this.highlight(i.index(s))
                } else 0 == s.length && this.removeHighlight()
            },
            loadMoreIfNeeded: function() {
                var e, t = this.results,
                    s = t.find("li.select2-more-results"),
                    i = this.resultsPage + 1,
                    n = this,
                    o = this.search.val(),
                    a = this.context;
                0 !== s.length && (e = s.offset().top - t.offset().top - t.height(), e <= this.opts.loadMorePadding && (s.addClass("select2-active"), this.opts.query({
                    element: this.opts.element,
                    term: o,
                    page: i,
                    context: a,
                    matcher: this.opts.matcher,
                    callback: this.bind(function(e) {
                        n.opened() && (n.opts.populateResults.call(this, t, e.results, {
                            term: o,
                            page: i,
                            context: a
                        }), n.postprocessResults(e, !1, !1), e.more === !0 ? (s.detach().appendTo(t).text(n.opts.formatLoadMore(i + 1)), window.setTimeout(function() {
                            n.loadMoreIfNeeded()
                        }, 10)) : s.remove(), n.positionDropdown(), n.resultsPage = i, n.context = e.context, this.opts.element.trigger({
                            type: "select2-loaded",
                            items: e
                        }))
                    })
                })))
            },
            tokenize: function() {},
            updateResults: function(s) {
                function i() {
                    l.removeClass("select2-active"), d.positionDropdown()
                }

                function n(e) {
                    h.html(e), i()
                }
                var a, r, c, l = this.search,
                    h = this.results,
                    u = this.opts,
                    d = this,
                    p = l.val(),
                    f = e.data(this.container, "select2-last-term");
                if ((s === !0 || !f || !o(p, f)) && (e.data(this.container, "select2-last-term", p), s === !0 || this.showSearchInput !== !1 && this.opened())) {
                    c = ++this.queryCount;
                    var g = this.getMaximumSelectionSize();
                    if (g >= 1 && (a = this.data(), e.isArray(a) && a.length >= g && x(u.formatSelectionTooBig, "formatSelectionTooBig"))) return void n("<li class='select2-selection-limit'>" + u.formatSelectionTooBig(g) + "</li>");
                    if (l.val().length < u.minimumInputLength) return n(x(u.formatInputTooShort, "formatInputTooShort") ? "<li class='select2-no-results'>" + u.formatInputTooShort(l.val(), u.minimumInputLength) + "</li>" : ""), void(s && this.showSearch && this.showSearch(!0));
                    if (u.maximumInputLength && l.val().length > u.maximumInputLength) return void n(x(u.formatInputTooLong, "formatInputTooLong") ? "<li class='select2-no-results'>" + u.formatInputTooLong(l.val(), u.maximumInputLength) + "</li>" : "");
                    u.formatSearching && 0 === this.findHighlightableChoices().length && n("<li class='select2-searching'>" + u.formatSearching() + "</li>"), l.addClass("select2-active"), this.removeHighlight(), r = this.tokenize(), r != t && null != r && l.val(r), this.resultsPage = 1, u.query({
                        element: u.element,
                        term: l.val(),
                        page: this.resultsPage,
                        context: null,
                        matcher: u.matcher,
                        callback: this.bind(function(a) {
                            var r;
                            if (c == this.queryCount) {
                                if (!this.opened()) return void this.search.removeClass("select2-active");
                                if (this.context = a.context === t ? null : a.context, this.opts.createSearchChoice && "" !== l.val() && (r = this.opts.createSearchChoice.call(d, l.val(), a.results), r !== t && null !== r && d.id(r) !== t && null !== d.id(r) && 0 === e(a.results).filter(function() {
                                        return o(d.id(this), d.id(r))
                                    }).length && a.results.unshift(r)), 0 === a.results.length && x(u.formatNoMatches, "formatNoMatches")) return void n("<li class='select2-no-results'>" + u.formatNoMatches(l.val()) + "</li>");
                                h.empty(), d.opts.populateResults.call(this, h, a.results, {
                                    term: l.val(),
                                    page: this.resultsPage,
                                    context: null
                                }), a.more === !0 && x(u.formatLoadMore, "formatLoadMore") && (h.append("<li class='select2-more-results'>" + d.opts.escapeMarkup(u.formatLoadMore(this.resultsPage)) + "</li>"), window.setTimeout(function() {
                                    d.loadMoreIfNeeded()
                                }, 10)), this.postprocessResults(a, s), i(), this.opts.element.trigger({
                                    type: "select2-loaded",
                                    items: a
                                })
                            }
                        })
                    })
                }
            },
            cancel: function() {
                this.close()
            },
            blur: function() {
                this.opts.selectOnBlur && this.selectHighlighted({
                    noFocus: !0
                }), this.close(), this.container.removeClass("select2-container-active"), this.search[0] === document.activeElement && this.search.blur(), this.clearSearch(), this.selection.find(".select2-search-choice-focus").removeClass("select2-search-choice-focus")
            },
            focusSearch: function() {
                p(this.search)
            },
            selectHighlighted: function(e) {
                var t = this.highlight(),
                    s = this.results.find(".select2-highlighted"),
                    i = s.closest(".select2-result").data("select2-data");
                i ? (this.highlight(t), this.onSelect(i, e)) : e && e.noFocus && this.close()
            },
            getPlaceholder: function() {
                var e;
                return this.opts.element.attr("placeholder") || this.opts.element.attr("data-placeholder") || this.opts.element.data("placeholder") || this.opts.placeholder || ((e = this.getPlaceholderOption()) !== t ? e.text() : t)
            },
            getPlaceholderOption: function() {
                if (this.select) {
                    var e = this.select.children("option").first();
                    if (this.opts.placeholderOption !== t) return "first" === this.opts.placeholderOption && e || "function" == typeof this.opts.placeholderOption && this.opts.placeholderOption(this.select);
                    if ("" === e.text() && "" === e.val()) return e
                }
            },
            initContainerWidth: function() {
                function s() {
                    var s, i, n, o, a, r;
                    if ("off" === this.opts.width) return null;
                    if ("element" === this.opts.width) return 0 === this.opts.element.outerWidth(!1) ? "auto" : this.opts.element.outerWidth(!1) + "px";
                    if ("copy" === this.opts.width || "resolve" === this.opts.width) {
                        if (s = this.opts.element.attr("style"), s !== t)
                            for (i = s.split(";"), o = 0, a = i.length; a > o; o += 1)
                                if (r = i[o].replace(/\s/g, ""), n = r.match(/^width:(([-+]?([0-9]*\.)?[0-9]+)(px|em|ex|%|in|cm|mm|pt|pc))/i), null !== n && n.length >= 1) return n[1];
                        return "resolve" === this.opts.width ? (s = this.opts.element.css("width"), s.indexOf("%") > 0 ? s : 0 === this.opts.element.outerWidth(!1) ? "auto" : this.opts.element.outerWidth(!1) + "px") : null
                    }
                    return e.isFunction(this.opts.width) ? this.opts.width() : this.opts.width
                }
                var i = s.call(this);
                null !== i && this.container.css("width", i)
            }
        }), R = I(A, {
            createContainer: function() {
                var t = e(document.createElement("div")).attr({
                    "class": "select2-container"
                }).html(["<a href='javascript:void(0)' onclick='return false;' class='select2-choice' tabindex='-1'>", "   <span class='select2-chosen'>&nbsp;</span><abbr class='select2-search-choice-close'></abbr>", "   <span class='select2-arrow'><b></b></span>", "</a>", "<input class='select2-focusser select2-offscreen' type='text'/>", "<div class='select2-drop select2-display-none'>", "   <div class='select2-search'>", "       <input type='text' autocomplete='off' autocorrect='off' autocapitalize='off' spellcheck='false' class='select2-input'/>", "   </div>", "   <ul class='select2-results'>", "   </ul>", "</div>"].join(""));
                return t
            },
            enableInterface: function() {
                this.parent.enableInterface.apply(this, arguments) && this.focusser.prop("disabled", !this.isInterfaceEnabled())
            },
            opening: function() {
                var s, i, n;
                this.opts.minimumResultsForSearch >= 0 && this.showSearch(!0), this.parent.opening.apply(this, arguments), this.showSearchInput !== !1 && this.search.val(this.focusser.val()), this.search.focus(), s = this.search.get(0), s.createTextRange ? (i = s.createTextRange(), i.collapse(!1), i.select()) : s.setSelectionRange && (n = this.search.val().length, s.setSelectionRange(n, n)), "" === this.search.val() && this.nextSearchTerm != t && (this.search.val(this.nextSearchTerm), this.search.select()), this.focusser.prop("disabled", !0).val(""), this.updateResults(!0), this.opts.element.trigger(e.Event("select2-open"))
            },
            close: function(e) {
                this.opened() && (this.parent.close.apply(this, arguments), e = e || {
                    focus: !0
                }, this.focusser.removeAttr("disabled"), e.focus && this.focusser.focus())
            },
            focus: function() {
                this.opened() ? this.close() : (this.focusser.removeAttr("disabled"), this.focusser.focus())
            },
            isFocused: function() {
                return this.container.hasClass("select2-container-active")
            },
            cancel: function() {
                this.parent.cancel.apply(this, arguments), this.focusser.removeAttr("disabled"), this.focusser.focus()
            },
            destroy: function() {
                e("label[for='" + this.focusser.attr("id") + "']").attr("for", this.opts.element.attr("id")), this.parent.destroy.apply(this, arguments)
            },
            initContainer: function() {
                var t, s = this.container,
                    i = this.dropdown;
                this.showSearch(this.opts.minimumResultsForSearch < 0 ? !1 : !0), this.selection = t = s.find(".select2-choice"), this.focusser = s.find(".select2-focusser"), this.focusser.attr("id", "s2id_autogen" + L()), e("label[for='" + this.opts.element.attr("id") + "']").attr("for", this.focusser.attr("id")), this.focusser.attr("tabindex", this.elementTabIndex), this.search.on("keydown", this.bind(function(e) {
                    if (this.isInterfaceEnabled()) {
                        if (e.which === P.PAGE_UP || e.which === P.PAGE_DOWN) return void g(e);
                        switch (e.which) {
                            case P.UP:
                            case P.DOWN:
                                return this.moveHighlight(e.which === P.UP ? -1 : 1), void g(e);
                            case P.ENTER:
                                return this.selectHighlighted(), void g(e);
                            case P.TAB:
                                return void this.selectHighlighted({
                                    noFocus: !0
                                });
                            case P.ESC:
                                return this.cancel(e), void g(e)
                        }
                    }
                })), this.search.on("blur", this.bind(function() {
                    document.activeElement === this.body().get(0) && window.setTimeout(this.bind(function() {
                        this.search.focus()
                    }), 0)
                })), this.focusser.on("keydown", this.bind(function(e) {
                    if (this.isInterfaceEnabled() && e.which !== P.TAB && !P.isControl(e) && !P.isFunctionKey(e) && e.which !== P.ESC) {
                        if (this.opts.openOnEnter === !1 && e.which === P.ENTER) return void g(e);
                        if (e.which == P.DOWN || e.which == P.UP || e.which == P.ENTER && this.opts.openOnEnter) {
                            if (e.altKey || e.ctrlKey || e.shiftKey || e.metaKey) return;
                            return this.open(), void g(e)
                        }
                        return e.which == P.DELETE || e.which == P.BACKSPACE ? (this.opts.allowClear && this.clear(), void g(e)) : void 0
                    }
                })), c(this.focusser), this.focusser.on("keyup-change input", this.bind(function(e) {
                    if (this.opts.minimumResultsForSearch >= 0) {
                        if (e.stopPropagation(), this.opened()) return;
                        this.open()
                    }
                })), t.on("mousedown", "abbr", this.bind(function(e) {
                    this.isInterfaceEnabled() && (this.clear(), m(e), this.close(), this.selection.focus())
                })), t.on("mousedown", this.bind(function(t) {
                    this.container.hasClass("select2-container-active") || this.opts.element.trigger(e.Event("select2-focus")), this.opened() ? this.close() : this.isInterfaceEnabled() && this.open(), g(t)
                })), i.on("mousedown", this.bind(function() {
                    this.search.focus()
                })), t.on("focus", this.bind(function(e) {
                    g(e)
                })), this.focusser.on("focus", this.bind(function() {
                    this.container.hasClass("select2-container-active") || this.opts.element.trigger(e.Event("select2-focus")), this.container.addClass("select2-container-active")
                })).on("blur", this.bind(function() {
                    this.opened() || (this.container.removeClass("select2-container-active"), this.opts.element.trigger(e.Event("select2-blur")))
                })), this.search.on("focus", this.bind(function() {
                    this.container.hasClass("select2-container-active") || this.opts.element.trigger(e.Event("select2-focus")), this.container.addClass("select2-container-active")
                })), this.initContainerWidth(), this.opts.element.addClass("select2-offscreen"), this.setPlaceholder()
            },
            clear: function(t) {
                var s = this.selection.data("select2-data");
                if (s) {
                    var i = e.Event("select2-clearing");
                    if (this.opts.element.trigger(i), i.isDefaultPrevented()) return;
                    var n = this.getPlaceholderOption();
                    this.opts.element.val(n ? n.val() : ""), this.selection.find(".select2-chosen").empty(), this.selection.removeData("select2-data"), this.setPlaceholder(), t !== !1 && (this.opts.element.trigger({
                        type: "select2-removed",
                        val: this.id(s),
                        choice: s
                    }), this.triggerChange({
                        removed: s
                    }))
                }
            },
            initSelection: function() {
                if (this.isPlaceholderOptionSelected()) this.updateSelection(null), this.close(), this.setPlaceholder();
                else {
                    var e = this;
                    this.opts.initSelection.call(null, this.opts.element, function(s) {
                        s !== t && null !== s && (e.updateSelection(s), e.close(), e.setPlaceholder())
                    })
                }
            },
            isPlaceholderOptionSelected: function() {
                var e;
                return this.getPlaceholder() ? (e = this.getPlaceholderOption()) !== t && e.prop("selected") || "" === this.opts.element.val() || this.opts.element.val() === t || null === this.opts.element.val() : !1
            },
            prepareOpts: function() {
                var t = this.parent.prepareOpts.apply(this, arguments),
                    s = this;
                return "select" === t.element.get(0).tagName.toLowerCase() ? t.initSelection = function(e, t) {
                    var i = e.find("option").filter(function() {
                        return this.selected
                    });
                    t(s.optionToData(i))
                } : "data" in t && (t.initSelection = t.initSelection || function(s, i) {
                    var n = s.val(),
                        a = null;
                    t.query({
                        matcher: function(e, s, i) {
                            var r = o(n, t.id(i));
                            return r && (a = i), r
                        },
                        callback: e.isFunction(i) ? function() {
                            i(a)
                        } : e.noop
                    })
                }), t
            },
            getPlaceholder: function() {
                return this.select && this.getPlaceholderOption() === t ? t : this.parent.getPlaceholder.apply(this, arguments)
            },
            setPlaceholder: function() {
                var e = this.getPlaceholder();
                if (this.isPlaceholderOptionSelected() && e !== t) {
                    if (this.select && this.getPlaceholderOption() === t) return;
                    this.selection.find(".select2-chosen").html(this.opts.escapeMarkup(e)), this.selection.addClass("select2-default"), this.container.removeClass("select2-allowclear")
                }
            },
            postprocessResults: function(e, t, s) {
                var i = 0,
                    n = this;
                if (this.findHighlightableChoices().each2(function(e, t) {
                        return o(n.id(t.data("select2-data")), n.opts.element.val()) ? (i = e, !1) : void 0
                    }), s !== !1 && this.highlight(t === !0 && i >= 0 ? i : 0), t === !0) {
                    var a = this.opts.minimumResultsForSearch;
                    a >= 0 && this.showSearch(O(e.results) >= a)
                }
            },
            showSearch: function(t) {
                this.showSearchInput !== t && (this.showSearchInput = t, this.dropdown.find(".select2-search").toggleClass("select2-search-hidden", !t), this.dropdown.find(".select2-search").toggleClass("select2-offscreen", !t), e(this.dropdown, this.container).toggleClass("select2-with-searchbox", t))
            },
            onSelect: function(e, t) {
                if (this.triggerSelect(e)) {
                    var s = this.opts.element.val(),
                        i = this.data();
                    this.opts.element.val(this.id(e)), this.updateSelection(e), this.opts.element.trigger({
                        type: "select2-selected",
                        val: this.id(e),
                        choice: e
                    }), this.nextSearchTerm = this.opts.nextSearchTerm(e, this.search.val()), this.close(), t && t.noFocus || this.focusser.focus(), o(s, this.id(e)) || this.triggerChange({
                        added: e,
                        removed: i
                    })
                }
            },
            updateSelection: function(e) {
                var s, i, n = this.selection.find(".select2-chosen");
                this.selection.data("select2-data", e), n.empty(), null !== e && (s = this.opts.formatSelection(e, n, this.opts.escapeMarkup)), s !== t && n.append(s), i = this.opts.formatSelectionCssClass(e, n), i !== t && n.addClass(i), this.selection.removeClass("select2-default"), this.opts.allowClear && this.getPlaceholder() !== t && this.container.addClass("select2-allowclear")
            },
            val: function() {
                var e, s = !1,
                    i = null,
                    n = this,
                    o = this.data();
                if (0 === arguments.length) return this.opts.element.val();
                if (e = arguments[0], arguments.length > 1 && (s = arguments[1]), this.select) this.select.val(e).find("option").filter(function() {
                    return this.selected
                }).each2(function(e, t) {
                    return i = n.optionToData(t), !1
                }), this.updateSelection(i), this.setPlaceholder(), s && this.triggerChange({
                    added: i,
                    removed: o
                });
                else {
                    if (!e && 0 !== e) return void this.clear(s);
                    if (this.opts.initSelection === t) throw new Error("cannot call val() if initSelection() is not defined");
                    this.opts.element.val(e), this.opts.initSelection(this.opts.element, function(e) {
                        n.opts.element.val(e ? n.id(e) : ""), n.updateSelection(e), n.setPlaceholder(), s && n.triggerChange({
                            added: e,
                            removed: o
                        })
                    })
                }
            },
            clearSearch: function() {
                this.search.val(""), this.focusser.val("")
            },
            data: function(e) {
                var s, i = !1;
                return 0 === arguments.length ? (s = this.selection.data("select2-data"), s == t && (s = null), s) : (arguments.length > 1 && (i = arguments[1]), void(e ? (s = this.data(), this.opts.element.val(e ? this.id(e) : ""), this.updateSelection(e), i && this.triggerChange({
                    added: e,
                    removed: s
                })) : this.clear(i)))
            }
        }), D = I(A, {
            createContainer: function() {
                var t = e(document.createElement("div")).attr({
                    "class": "select2-container select2-container-multi"
                }).html(["<ul class='select2-choices'>", "  <li class='select2-search-field'>", "    <input type='text' autocomplete='off' autocorrect='off' autocapitalize='off' spellcheck='false' class='select2-input'>", "  </li>", "</ul>", "<div class='select2-drop select2-drop-multi select2-display-none'>", "   <ul class='select2-results'>", "   </ul>", "</div>"].join(""));
                return t
            },
            prepareOpts: function() {
                var t = this.parent.prepareOpts.apply(this, arguments),
                    s = this;
                return "select" === t.element.get(0).tagName.toLowerCase() ? t.initSelection = function(e, t) {
                    var i = [];
                    e.find("option").filter(function() {
                        return this.selected
                    }).each2(function(e, t) {
                        i.push(s.optionToData(t))
                    }), t(i)
                } : "data" in t && (t.initSelection = t.initSelection || function(s, i) {
                    var n = a(s.val(), t.separator),
                        r = [];
                    t.query({
                        matcher: function(s, i, a) {
                            var c = e.grep(n, function(e) {
                                return o(e, t.id(a))
                            }).length;
                            return c && r.push(a), c
                        },
                        callback: e.isFunction(i) ? function() {
                            for (var e = [], s = 0; s < n.length; s++)
                                for (var a = n[s], c = 0; c < r.length; c++) {
                                    var l = r[c];
                                    if (o(a, t.id(l))) {
                                        e.push(l), r.splice(c, 1);
                                        break
                                    }
                                }
                            i(e)
                        } : e.noop
                    })
                }), t
            },
            selectChoice: function(e) {
                var t = this.container.find(".select2-search-choice-focus");
                t.length && e && e[0] == t[0] || (t.length && this.opts.element.trigger("choice-deselected", t), t.removeClass("select2-search-choice-focus"), e && e.length && (this.close(), e.addClass("select2-search-choice-focus"), this.opts.element.trigger("choice-selected", e)))
            },
            destroy: function() {
                e("label[for='" + this.search.attr("id") + "']").attr("for", this.opts.element.attr("id")), this.parent.destroy.apply(this, arguments)
            },
            initContainer: function() {
                var t, s = ".select2-choices";
                this.searchContainer = this.container.find(".select2-search-field"), this.selection = t = this.container.find(s);
                var i = this;
                this.selection.on("click", ".select2-search-choice:not(.select2-locked)", function() {
                    i.search[0].focus(), i.selectChoice(e(this))
                }), this.search.attr("id", "s2id_autogen" + L()), e("label[for='" + this.opts.element.attr("id") + "']").attr("for", this.search.attr("id")), this.search.on("input paste", this.bind(function() {
                    this.isInterfaceEnabled() && (this.opened() || this.open())
                })), this.search.attr("tabindex", this.elementTabIndex), this.keydowns = 0, this.search.on("keydown", this.bind(function(e) {
                    if (this.isInterfaceEnabled()) {
                        ++this.keydowns;
                        var s = t.find(".select2-search-choice-focus"),
                            i = s.prev(".select2-search-choice:not(.select2-locked)"),
                            n = s.next(".select2-search-choice:not(.select2-locked)"),
                            o = f(this.search);
                        if (s.length && (e.which == P.LEFT || e.which == P.RIGHT || e.which == P.BACKSPACE || e.which == P.DELETE || e.which == P.ENTER)) {
                            var a = s;
                            return e.which == P.LEFT && i.length ? a = i : e.which == P.RIGHT ? a = n.length ? n : null : e.which === P.BACKSPACE ? (this.unselect(s.first()), this.search.width(10), a = i.length ? i : n) : e.which == P.DELETE ? (this.unselect(s.first()), this.search.width(10), a = n.length ? n : null) : e.which == P.ENTER && (a = null), this.selectChoice(a), g(e), void(a && a.length || this.open())
                        }
                        if ((e.which === P.BACKSPACE && 1 == this.keydowns || e.which == P.LEFT) && 0 == o.offset && !o.length) return this.selectChoice(t.find(".select2-search-choice:not(.select2-locked)").last()), void g(e);
                        if (this.selectChoice(null), this.opened()) switch (e.which) {
                            case P.UP:
                            case P.DOWN:
                                return this.moveHighlight(e.which === P.UP ? -1 : 1), void g(e);
                            case P.ENTER:
                                return this.selectHighlighted(), void g(e);
                            case P.TAB:
                                return this.selectHighlighted({
                                    noFocus: !0
                                }), void this.close();
                            case P.ESC:
                                return this.cancel(e), void g(e)
                        }
                        if (e.which !== P.TAB && !P.isControl(e) && !P.isFunctionKey(e) && e.which !== P.BACKSPACE && e.which !== P.ESC) {
                            if (e.which === P.ENTER) {
                                if (this.opts.openOnEnter === !1) return;
                                if (e.altKey || e.ctrlKey || e.shiftKey || e.metaKey) return
                            }
                            this.open(), (e.which === P.PAGE_UP || e.which === P.PAGE_DOWN) && g(e), e.which === P.ENTER && g(e)
                        }
                    }
                })), this.search.on("keyup", this.bind(function() {
                    this.keydowns = 0, this.resizeSearch()
                })), this.search.on("blur", this.bind(function(t) {
                    this.container.removeClass("select2-container-active"), this.search.removeClass("select2-focused"), this.selectChoice(null), this.opened() || this.clearSearch(), t.stopImmediatePropagation(), this.opts.element.trigger(e.Event("select2-blur"))
                })), this.container.on("click", s, this.bind(function(t) {
                    this.isInterfaceEnabled() && (e(t.target).closest(".select2-search-choice").length > 0 || (this.selectChoice(null), this.clearPlaceholder(), this.container.hasClass("select2-container-active") || this.opts.element.trigger(e.Event("select2-focus")), this.open(), this.focusSearch(), t.preventDefault()))
                })), this.container.on("focus", s, this.bind(function() {
                    this.isInterfaceEnabled() && (this.container.hasClass("select2-container-active") || this.opts.element.trigger(e.Event("select2-focus")), this.container.addClass("select2-container-active"), this.dropdown.addClass("select2-drop-active"), this.clearPlaceholder())
                })), this.initContainerWidth(), this.opts.element.addClass("select2-offscreen"), this.clearSearch()
            },
            enableInterface: function() {
                this.parent.enableInterface.apply(this, arguments) && this.search.prop("disabled", !this.isInterfaceEnabled())
            },
            initSelection: function() {
                if ("" === this.opts.element.val() && "" === this.opts.element.text() && (this.updateSelection([]), this.close(), this.clearSearch()), this.select || "" !== this.opts.element.val()) {
                    var e = this;
                    this.opts.initSelection.call(null, this.opts.element, function(s) {
                        s !== t && null !== s && (e.updateSelection(s), e.close(), e.clearSearch())
                    })
                }
            },
            clearSearch: function() {
                var e = this.getPlaceholder(),
                    s = this.getMaxSearchWidth();
                e !== t && 0 === this.getVal().length && this.search.hasClass("select2-focused") === !1 ? (this.search.val(e).addClass("select2-default"), this.search.width(s > 0 ? s : this.container.css("width"))) : this.search.val("").width(10)
            },
            clearPlaceholder: function() {
                this.search.hasClass("select2-default") && this.search.val("").removeClass("select2-default")
            },
            opening: function() {
                this.clearPlaceholder(), this.resizeSearch(), this.parent.opening.apply(this, arguments), this.focusSearch(), this.updateResults(!0), this.search.focus(), this.opts.element.trigger(e.Event("select2-open"))
            },
            close: function() {
                this.opened() && this.parent.close.apply(this, arguments)
            },
            focus: function() {
                this.close(), this.search.focus()
            },
            isFocused: function() {
                return this.search.hasClass("select2-focused")
            },
            updateSelection: function(t) {
                var s = [],
                    n = [],
                    o = this;
                e(t).each(function() {
                    i(o.id(this), s) < 0 && (s.push(o.id(this)), n.push(this))
                }), t = n, this.selection.find(".select2-search-choice").remove(), e(t).each(function() {
                    o.addSelectedChoice(this)
                }), o.postprocessResults()
            },
            tokenize: function() {
                var e = this.search.val();
                e = this.opts.tokenizer.call(this, e, this.data(), this.bind(this.onSelect), this.opts), null != e && e != t && (this.search.val(e), e.length > 0 && this.open())
            },
            onSelect: function(e, t) {
                this.triggerSelect(e) && (this.addSelectedChoice(e), this.opts.element.trigger({
                    type: "selected",
                    val: this.id(e),
                    choice: e
                }), (this.select || !this.opts.closeOnSelect) && this.postprocessResults(e, !1, this.opts.closeOnSelect === !0), this.opts.closeOnSelect ? (this.close(), this.search.width(10)) : this.countSelectableResults() > 0 ? (this.search.width(10), this.resizeSearch(), this.getMaximumSelectionSize() > 0 && this.val().length >= this.getMaximumSelectionSize() && this.updateResults(!0), this.positionDropdown()) : (this.close(), this.search.width(10)), this.triggerChange({
                    added: e
                }), t && t.noFocus || this.focusSearch())
            },
            cancel: function() {
                this.close(), this.focusSearch()
            },
            addSelectedChoice: function(s) {
                var i, n, o = !s.locked,
                    a = e("<li class='select2-search-choice'>    <div></div>    <a href='#' onclick='return false;' class='select2-search-choice-close' tabindex='-1'></a></li>"),
                    r = e("<li class='select2-search-choice select2-locked'><div></div></li>"),
                    c = o ? a : r,
                    l = this.id(s),
                    h = this.getVal();
                i = this.opts.formatSelection(s, c.find("div"), this.opts.escapeMarkup), i != t && c.find("div").replaceWith("<div>" + i + "</div>"), n = this.opts.formatSelectionCssClass(s, c.find("div")), n != t && c.addClass(n), o && c.find(".select2-search-choice-close").on("mousedown", g).on("click dblclick", this.bind(function(t) {
                    this.isInterfaceEnabled() && (e(t.target).closest(".select2-search-choice").fadeOut("fast", this.bind(function() {
                        this.unselect(e(t.target)), this.selection.find(".select2-search-choice-focus").removeClass("select2-search-choice-focus"), this.close(), this.focusSearch()
                    })).dequeue(), g(t))
                })).on("focus", this.bind(function() {
                    this.isInterfaceEnabled() && (this.container.addClass("select2-container-active"), this.dropdown.addClass("select2-drop-active"))
                })), c.data("select2-data", s), c.insertBefore(this.searchContainer), h.push(l), this.setVal(h)
            },
            unselect: function(t) {
                var s, n, o = this.getVal();
                if (t = t.closest(".select2-search-choice"), 0 === t.length) throw "Invalid argument: " + t + ". Must be .select2-search-choice";
                if (s = t.data("select2-data")) {
                    for (;
                        (n = i(this.id(s), o)) >= 0;) o.splice(n, 1), this.setVal(o), this.select && this.postprocessResults();
                    var a = e.Event("select2-removing");
                    a.val = this.id(s), a.choice = s, this.opts.element.trigger(a), a.isDefaultPrevented() || (t.remove(), this.opts.element.trigger({
                        type: "select2-removed",
                        val: this.id(s),
                        choice: s
                    }), this.triggerChange({
                        removed: s
                    }))
                }
            },
            postprocessResults: function(e, t, s) {
                var n = this.getVal(),
                    o = this.results.find(".select2-result"),
                    a = this.results.find(".select2-result-with-children"),
                    r = this;
                o.each2(function(e, t) {
                    var s = r.id(t.data("select2-data"));
                    i(s, n) >= 0 && (t.addClass("select2-selected"), t.find(".select2-result-selectable").addClass("select2-selected"))
                }), a.each2(function(e, t) {
                    t.is(".select2-result-selectable") || 0 !== t.find(".select2-result-selectable:not(.select2-selected)").length || t.addClass("select2-selected")
                }), -1 == this.highlight() && s !== !1 && r.highlight(0), !this.opts.createSearchChoice && !o.filter(".select2-result:not(.select2-selected)").length > 0 && (!e || e && !e.more && 0 === this.results.find(".select2-no-results").length) && x(r.opts.formatNoMatches, "formatNoMatches") && this.results.append("<li class='select2-no-results'>" + r.opts.formatNoMatches(r.search.val()) + "</li>")
            },
            getMaxSearchWidth: function() {
                return this.selection.width() - r(this.search)
            },
            resizeSearch: function() {
                var e, t, s, i, n, o = r(this.search);
                e = v(this.search) + 10, t = this.search.offset().left, s = this.selection.width(), i = this.selection.offset().left, n = s - (t - i) - o, e > n && (n = s - o), 40 > n && (n = s - o), 0 >= n && (n = e), this.search.width(Math.floor(n))
            },
            getVal: function() {
                var e;
                return this.select ? (e = this.select.val(), null === e ? [] : e) : (e = this.opts.element.val(), a(e, this.opts.separator))
            },
            setVal: function(t) {
                var s;
                this.select ? this.select.val(t) : (s = [], e(t).each(function() {
                    i(this, s) < 0 && s.push(this)
                }), this.opts.element.val(0 === s.length ? "" : s.join(this.opts.separator)))
            },
            buildChangeDetails: function(e, t) {
                for (var t = t.slice(0), e = e.slice(0), s = 0; s < t.length; s++)
                    for (var i = 0; i < e.length; i++) o(this.opts.id(t[s]), this.opts.id(e[i])) && (t.splice(s, 1), s > 0 && s--, e.splice(i, 1), i--);
                return {
                    added: t,
                    removed: e
                }
            },
            val: function(s, i) {
                var n, o = this;
                if (0 === arguments.length) return this.getVal();
                if (n = this.data(), n.length || (n = []), !s && 0 !== s) return this.opts.element.val(""), this.updateSelection([]), this.clearSearch(), void(i && this.triggerChange({
                    added: this.data(),
                    removed: n
                }));
                if (this.setVal(s), this.select) this.opts.initSelection(this.select, this.bind(this.updateSelection)), i && this.triggerChange(this.buildChangeDetails(n, this.data()));
                else {
                    if (this.opts.initSelection === t) throw new Error("val() cannot be called if initSelection() is not defined");
                    this.opts.initSelection(this.opts.element, function(t) {
                        var s = e.map(t, o.id);
                        o.setVal(s), o.updateSelection(t), o.clearSearch(), i && o.triggerChange(o.buildChangeDetails(n, o.data()))
                    })
                }
                this.clearSearch()
            },
            onSortStart: function() {
                if (this.select) throw new Error("Sorting of elements is not supported when attached to <select>. Attach to <input type='hidden'/> instead.");
                this.search.width(0), this.searchContainer.hide()
            },
            onSortEnd: function() {
                var t = [],
                    s = this;
                this.searchContainer.show(), this.searchContainer.appendTo(this.searchContainer.parent()), this.resizeSearch(), this.selection.find(".select2-search-choice").each(function() {
                    t.push(s.opts.id(e(this).data("select2-data")))
                }), this.setVal(t), this.triggerChange()
            },
            data: function(t, s) {
                var i, n, o = this;
                return 0 === arguments.length ? this.selection.find(".select2-search-choice").map(function() {
                    return e(this).data("select2-data")
                }).get() : (n = this.data(), t || (t = []), i = e.map(t, function(e) {
                    return o.opts.id(e)
                }), this.setVal(i), this.updateSelection(t), this.clearSearch(), void(s && this.triggerChange(this.buildChangeDetails(n, this.data()))))
            }
        }), e.fn.select2 = function() {
            var s, n, o, a, r, c = Array.prototype.slice.call(arguments, 0),
                l = ["val", "destroy", "opened", "open", "close", "focus", "isFocused", "container", "dropdown", "onSortStart", "onSortEnd", "enable", "disable", "readonly", "positionDropdown", "data", "search"],
                h = ["opened", "isFocused", "container", "dropdown"],
                u = ["val", "data"],
                d = {
                    search: "externalSearch"
                };
            return this.each(function() {
                if (0 === c.length || "object" == typeof c[0]) s = 0 === c.length ? {} : e.extend({}, c[0]), s.element = e(this), "select" === s.element.get(0).tagName.toLowerCase() ? r = s.element.prop("multiple") : (r = s.multiple || !1, "tags" in s && (s.multiple = r = !0)), n = r ? new D : new R, n.init(s);
                else {
                    if ("string" != typeof c[0]) throw "Invalid arguments to select2 plugin: " + c;
                    if (i(c[0], l) < 0) throw "Unknown method: " + c[0];
                    if (a = t, n = e(this).data("select2"), n === t) return;
                    if (o = c[0], "container" === o ? a = n.container : "dropdown" === o ? a = n.dropdown : (d[o] && (o = d[o]), a = n[o].apply(n, c.slice(1))), i(c[0], h) >= 0 || i(c[0], u) && 1 == c.length) return !1
                }
            }), a === t ? this : a
        }, e.fn.select2.defaults = {
            width: "copy",
            loadMorePadding: 0,
            closeOnSelect: !0,
            openOnEnter: !0,
            containerCss: {},
            dropdownCss: {},
            containerCssClass: "",
            dropdownCssClass: "",
            formatResult: function(e, t, s, i) {
                var n = [];
                return C(e.text, s.term, n, i), n.join("")
            },
            formatSelection: function(e, s, i) {
                return e ? i(e.text) : t
            },
            sortResults: function(e) {
                return e
            },
            formatResultCssClass: function() {
                return t
            },
            formatSelectionCssClass: function() {
                return t
            },
            formatNoMatches: function() {
                return "No matches found"
            },
            formatInputTooShort: function(e, t) {
                var s = t - e.length;
                return "Please enter " + s + " more character" + (1 == s ? "" : "s")
            },
            formatInputTooLong: function(e, t) {
                var s = e.length - t;
                return "Please delete " + s + " character" + (1 == s ? "" : "s")
            },
            formatSelectionTooBig: function(e) {
                return "You can only select " + e + " item" + (1 == e ? "" : "s")
            },
            formatLoadMore: function() {
                return "Loading more results..."
            },
            formatSearching: function() {
                return "Searching..."
            },
            minimumResultsForSearch: 0,
            minimumInputLength: 0,
            maximumInputLength: null,
            maximumSelectionSize: 0,
            id: function(e) {
                return e.id
            },
            matcher: function(e, t) {
            	return s("" + t).toUpperCase().indexOf(s("" + e).toUpperCase()) >= 0
            },
            separator: ",",
            tokenSeparators: [],
            tokenizer: k,
            escapeMarkup: b,
            blurOnChange: !1,
            selectOnBlur: !1,
            adaptContainerCssClass: function(e) {
                return e
            },
            adaptDropdownCssClass: function() {
                return null
            },
            nextSearchTerm: function() {
                return t
            }
        }, e.fn.select2.ajaxDefaults = {
            transport: e.ajax,
            params: {
                type: "GET",
                cache: !1,
                dataType: "json"
            }
        }, window.Select2 = {
            query: {
                ajax: S,
                local: y,
                tags: E
            },
            util: {
                debounce: h,
                markMatch: C,
                escapeMarkup: b,
                stripDiacritics: s
            },
            "class": {
                "abstract": A,
                single: R,
                multi: D
            }
        }
    }
}(jQuery),
function(e) {
    "use strict";
    e.extend(e.fn.select2.defaults, {
        formatNoMatches: function() {
            return "\u8a72\u5f53\u9805\u76ee\u306a\u3057"
        },
        formatInputTooShort: function(e, t) {
            var s = t - e.length;
            return "\u5f8c" + s + "\u6587\u5b57\u5165\u308c\u3066\u304f\u3060\u3055\u3044"
        },
        formatInputTooLong: function(e, t) {
            var s = e.length - t;
            return "\u691c\u7d22\u6587\u5b57\u5217\u304c" + s + "\u6587\u5b57\u9577\u3059\u304e\u307e\u3059"
        },
        formatSelectionTooBig: function(e) {
        	return "\u6700\u5927\u3067" + e + "\u3064\u307E\u3067\u3057\u304B\u767B\u9332\u3067\u304D\u307E\u305B\u3093\u3002"
            //return "\u6700\u5927\u3067" + e + "\u9805\u76EE\u307E\u3067\u3057\u304B\u9078\u629E\u3067\u304D\u307E\u305B\u3093"
        },
        formatLoadMore: function() {
            return "\u8aad\u8fbc\u4e2d\uff65\uff65\uff65"
        },
        formatSearching: function() {
            return "\u691c\u7d22\u4e2d\uff65\uff65\uff65"
        }
    })
}(jQuery);