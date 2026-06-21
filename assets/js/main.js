/* FoodFlow - main.js
   Cart, form validation, theme toggle, mobile nav
   CIT6224 Group 19 - No external libraries */

'use strict';

var cart = [];
try { cart = JSON.parse(localStorage.getItem('ff_cart') || '[]'); } catch(e) { cart = []; }

function saveCart() {
    localStorage.setItem('ff_cart', JSON.stringify(cart));
    updateCartBadge();
}

function updateCartBadge() {
    var total = cart.reduce(function(s,i){ return s + i.qty; }, 0);
    document.querySelectorAll('#cart-count').forEach(function(el){ el.textContent = total; });
}

function addToCart(item) {
    if (cart.length > 0 && cart[0].restaurant_id !== item.restaurant_id) {
        if (!confirm('Adding this item will clear your cart from another restaurant. Continue?')) return;
        cart = [];
    }
    var found = false;
    for (var i = 0; i < cart.length; i++) {
        if (cart[i].menu_item_id === item.menu_item_id) { cart[i].qty += 1; found = true; break; }
    }
    if (!found) cart.push({ restaurant_id:item.restaurant_id, menu_item_id:item.menu_item_id, name:item.name, price:item.price, qty:1 });
    saveCart();
    renderCart();
    toast(item.name + ' added to cart!');
}

function updateQty(menu_item_id, delta) {
    for (var i = 0; i < cart.length; i++) {
        if (cart[i].menu_item_id === menu_item_id) {
            cart[i].qty += delta;
            if (cart[i].qty <= 0) cart.splice(i, 1);
            break;
        }
    }
    saveCart();
    renderCart();
}

function renderCart() {
    var container  = document.getElementById('cart-items');
    var totalEl    = document.getElementById('cart-total');
    var hiddenData = document.getElementById('checkout-data');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '<p class="text-muted fs-sm" style="padding:10px 0;">Your cart is empty.</p>';
        if (totalEl)    totalEl.textContent = '0.00';
        if (hiddenData) hiddenData.value    = '[]';
        return;
    }

    var html = '', subtotal = 0, isCheckout = !!hiddenData;
    cart.forEach(function(item) {
        var line = item.price * item.qty;
        subtotal += line;
        html += '<div class="cart-item">'
            + '<div style="flex:1;"><strong style="font-size:0.9rem;">' + esc(item.name) + '</strong><br>'
            + '<span class="text-muted fs-sm">RM ' + item.price.toFixed(2) + '</span></div>'
            + '<div class="qty-controls">'
            + '<button type="button" class="qty-btn" onclick="updateQty(' + item.menu_item_id + ',-1)">&#8722;</button>'
            + '<span style="min-width:24px;text-align:center;font-weight:600;">' + item.qty + '</span>'
            + '<button type="button" class="qty-btn" onclick="updateQty(' + item.menu_item_id + ',1)">&#43;</button>'
            + '</div>'
            + '<div style="font-weight:600;min-width:60px;text-align:right;">RM ' + line.toFixed(2) + '</div>'
            + '</div>';
    });
    container.innerHTML = html;
    if (hiddenData) hiddenData.value = JSON.stringify(cart);
    if (totalEl) totalEl.textContent = (isCheckout ? subtotal + 5.00 : subtotal).toFixed(2);
}

function esc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

function toast(msg) {
    var el = document.getElementById('ff-toast');
    if (!el) { el = document.createElement('div'); el.id = 'ff-toast'; document.body.appendChild(el); }
    el.textContent = msg;
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(function(){ el.style.opacity = '0'; }, 2600);
}

function toggleTheme() {
    var light = document.body.classList.toggle('light-mode');
    localStorage.setItem('ff_theme', light ? 'light' : 'dark');
}
(function(){ if (localStorage.getItem('ff_theme') === 'light') document.body.classList.add('light-mode'); })();

document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('navToggle');
    var nav    = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            var open = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }
    updateCartBadge();
    renderCart();
    initValidation();
});

/* ── Form Validation ── */
function initValidation() {
    var reg = document.getElementById('register-form');
    if (reg) {
        wireBlur(reg,'name',           ruleMinLen(2,'Full name must be at least 2 characters.'));
        wireBlur(reg,'email',          ruleEmail);
        wireBlur(reg,'phone',          rulePhone);
        wireBlur(reg,'password',       rulePassword);
        wireBlur(reg,'password_confirm', ruleConfirm(reg));
        reg.addEventListener('submit', function(e) {
            var ok = true;
            if (!checkField(reg,'name',            ruleMinLen(2,'Full name must be at least 2 characters.'))) ok=false;
            if (!checkField(reg,'email',           ruleEmail))        ok=false;
            if (!checkField(reg,'phone',           rulePhone))        ok=false;
            if (!checkField(reg,'password',        rulePassword))     ok=false;
            if (!checkField(reg,'password_confirm',ruleConfirm(reg))) ok=false;
            if (!ok) e.preventDefault();
        });
    }

    var login = document.getElementById('login-form');
    if (login) {
        wireBlur(login,'email',   ruleEmail);
        wireBlur(login,'password',ruleRequired('Password is required.'));
        login.addEventListener('submit', function(e) {
            var ok = true;
            if (!checkField(login,'email',   ruleEmail))                         ok=false;
            if (!checkField(login,'password',ruleRequired('Password is required.'))) ok=false;
            if (!ok) e.preventDefault();
        });
    }

    var checkout = document.getElementById('checkout-form');
    if (checkout) {
        wireBlur(checkout,'address',ruleMinLen(10,'Please enter a full delivery address (at least 10 characters).'));
        checkout.addEventListener('submit', function(e) {
            var ok = true;
            if (!checkField(checkout,'address',ruleMinLen(10,'Please enter a full delivery address (at least 10 characters).'))) ok=false;
            if (cart.length === 0) { toast('Your cart is empty. Add items first.'); ok=false; }
            if (!ok) e.preventDefault();
        });
    }

    var profile = document.getElementById('profile-form');
    if (profile) {
        wireBlur(profile,'name', ruleMinLen(2,'Full name must be at least 2 characters.'));
        wireBlur(profile,'phone',rulePhone);
        profile.addEventListener('submit', function(e) {
            var ok = true;
            if (!checkField(profile,'name', ruleMinLen(2,'Full name must be at least 2 characters.'))) ok=false;
            if (!checkField(profile,'phone',rulePhone)) ok=false;
            if (!ok) e.preventDefault();
        });
    }
}

function wireBlur(form,name,rule) {
    var el = form.querySelector('[name="'+name+'"]');
    if (el) el.addEventListener('blur', function(){ checkField(form,name,rule); });
}
function checkField(form,name,rule) {
    var el = form.querySelector('[name="'+name+'"]');
    if (!el) return true;
    var err = rule(el.value.trim(), form);
    if (err) { setErr(el,err); return false; }
    clearErr(el); return true;
}
function setErr(el,msg) {
    el.classList.add('is-error'); el.classList.remove('is-success');
    var s = el.parentElement.querySelector('.field-error');
    if (!s) { s = document.createElement('span'); s.className='field-error'; el.parentElement.appendChild(s); }
    s.textContent = msg;
}
function clearErr(el) {
    el.classList.remove('is-error'); el.classList.add('is-success');
    var s = el.parentElement.querySelector('.field-error');
    if (s) s.remove();
}

function ruleRequired(msg) { return function(v){ return v ? '' : (msg||'Required.'); }; }
function ruleMinLen(min,msg) { return function(v){ return (!v||v.length<min) ? (msg||'Min '+min+' chars.') : ''; }; }
function ruleEmail(v) {
    if (!v) return 'Email address is required.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Please enter a valid email address.';
    return '';
}
function rulePhone(v) {
    if (!v) return '';
    if (!/^[\d\s\-\+\(\)]{7,15}$/.test(v)) return 'Phone must be 7 to 15 digits.';
    return '';
}
function rulePassword(v) {
    if (!v)           return 'Password is required.';
    if (v.length < 8) return 'Password must be at least 8 characters.';
    if (!/[A-Za-z]/.test(v)) return 'Password must contain at least one letter.';
    if (!/[0-9]/.test(v))    return 'Password must contain at least one number.';
    return '';
}
function ruleConfirm(form) {
    return function(v) {
        var pw = form.querySelector('[name="password"]');
        if (!v) return 'Please confirm your password.';
        if (pw && v !== pw.value) return 'Passwords do not match.';
        return '';
    };
}
