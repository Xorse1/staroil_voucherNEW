const fallbackProducts = [
  { id: "fuel-10", amount: 10, status: "Available", stock: 210, image: "images/10cedisvoucher.png" },
  { id: "fuel-20", amount: 20, status: "Available", stock: 196, image: "images/20cedisvoucher.png" },
  { id: "fuel-30", amount: 30, status: "Available", stock: 175, image: "images/30cedisvoucher.png" },
  { id: "fuel-40", amount: 40, status: "Available", stock: 164, image: "images/40cedisvoucher.png" },
  { id: "fuel-50", amount: 50, status: "Available", stock: 148, image: "images/50cedisvoucher.png" },
  { id: "fuel-100", amount: 100, status: "Available", stock: 118, image: "images/100cedisvoucher.png" },
  { id: "fuel-200", amount: 200, status: "Available", stock: 94, image: "images/200cedisvoucher.png" },
  { id: "fuel-500", amount: 500, status: "Limited", stock: 22, image: "images/500cedisvoucher.png" },
  { id: "fuel-1000", amount: 1000, status: "Corporate", stock: 16, image: "images/1000cedisvoucher.png" }
];

let products = [...fallbackProducts];
let authState = { loggedIn: false, name: "", otpStatus: 0, authenticatorStatus: 0, showMfaSetupReminder: false, appCode: "", partnerFee: 0 };

let vouchers = JSON.parse(localStorage.getItem("staroil:vouchers") || "null") || [
  { code: "SO-FV-104280", amount: 200, date: "2026-06-02", status: "Pending" },
  { code: "SO-FV-104281", amount: 500, date: "2026-06-01", status: "Activated" },
  { code: "SO-FV-104141", amount: 100, date: "2026-05-28", status: "Redeemed" },
  { code: "SO-FV-103904", amount: 1000, date: "2026-05-12", status: "Expired" }
];
let vouchersLoadedFromEndpoint = false;
let voucherView = localStorage.getItem("staroil:voucherView") || "grid";
let walletBalance = null;
let walletBalanceVisible = localStorage.getItem("staroil:walletBalanceVisible") !== "false";

const money = (value) => `GHS ${value.toLocaleString("en-GH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const cart = () => JSON.parse(localStorage.getItem("staroil:cart") || "[]");
const getCartId = () => {
  let id = localStorage.getItem("staroil:cartId");
  if (!id) {
    id = (window.crypto?.randomUUID ? window.crypto.randomUUID() : `${Date.now()}-${Math.random().toString(16).slice(2)}`).replace(/[^a-zA-Z0-9_-]/g, "");
    localStorage.setItem("staroil:cartId", id);
  }
  return id;
};
const saveCart = (items, event = "cart_update") => {
  localStorage.setItem("staroil:cart", JSON.stringify(items));
  trackCartEvent(event, items);
};
const saveVouchers = () => localStorage.setItem("staroil:vouchers", JSON.stringify(vouchers));
const subtotal = () => cart().reduce((sum, item) => sum + item.amount * item.quantity, 0);
const discountConfig = { rate: 0, minAmount: 200, maxCap: 20000 };
const discount = () => {
  const amount = subtotal();
  if (amount < discountConfig.minAmount) return 0;
  return Math.round(Math.min(amount, discountConfig.maxCap) * discountConfig.rate * 100) / 100;
};
const voucherTotal = () => subtotal() - discount();
const partnerFeePercent = () => Math.max(0, Number(authState.partnerFee || 0));
const partnerFeeAmount = () => Math.round((voucherTotal() * partnerFeePercent()) / 100 * 100) / 100;
const total = () => Math.round((voucherTotal() + partnerFeeAmount()) * 100) / 100;
const count = () => cart().reduce((sum, item) => sum + item.quantity, 0);
const escapeHtml = (value) => String(value ?? "").replace(/[&<>"']/g, (char) => ({
  "&": "&amp;",
  "<": "&lt;",
  ">": "&gt;",
  '"': "&quot;",
  "'": "&#039;"
}[char]));

function loadingSpinner(message = "Loading...") {
  return `
    <div class="staroil-loading" role="status" aria-live="polite">
      <span class="staroil-spinner" aria-hidden="true"></span>
      <span>${escapeHtml(message)}</span>
    </div>`;
}

let cartTrackTimer = null;
const activityTracker = {
  endpoint: "user_activity_track",
  queue: [],
  flushTimer: null,
  heartbeatTimer: null,
  startedAt: Date.now(),
  lastActiveAt: Date.now(),
  activeMs: 0,
  maxScroll: 0,
  lastScrollAt: 0,
  lastMouse: { x: 0, y: 0 },
  drag: null,
  initialized: false
};

function ensureRuntimeStyles() {
  if (document.getElementById("staroil-runtime-styles")) return;

  const style = document.createElement("style");
  style.id = "staroil-runtime-styles";
  style.textContent = `
    @keyframes staroil-spin {
      to { transform: rotate(360deg); }
    }

    .staroil-spinner {
      display: inline-block;
      width: 28px;
      height: 28px;
      border: 3px solid rgba(33,120,189,.18);
      border-top-color: #2178BD;
      border-radius: 999px;
      animation: staroil-spin 0.75s linear infinite;
    }

    .staroil-loading {
      display: flex;
      min-height: 96px;
      align-items: center;
      justify-content: center;
      gap: 12px;
      color: #64748B;
      font-size: 14px;
      font-weight: 700;
    }

    .lube-floating-link {
      transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease, background-color 180ms ease;
      will-change: transform;
    }

    .lube-floating-link svg {
      transition: transform 180ms ease;
    }

    .lube-floating-link:hover,
    .lube-floating-link:focus-visible {
      box-shadow: 0 18px 34px rgba(21, 37, 58, 0.18);
      border-color: #2178BD;
    }

    .lube-floating-link:hover svg,
    .lube-floating-link:focus-visible svg {
      transform: rotate(-8deg) scale(1.08);
    }

    .lube-floating-link:focus-visible {
      outline: 2px solid #2178BD;
      outline-offset: 3px;
    }

    @media (min-width: 1024px) {
      .lube-floating-link:hover,
      .lube-floating-link:focus-visible {
        transform: translateY(-50%) translateX(-4px) scale(1.03);
      }
    }

    @media (max-width: 1023px) {
      .lube-floating-link:hover,
      .lube-floating-link:focus-visible {
        transform: translateY(-4px) scale(1.03);
      }
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      background-image: url("images/alogo_light.png");
      background-repeat: repeat;
      background-position: 22px 18px;
      background-size: 138px auto;
      opacity: 0.045;
    }

    body > * {
      position: relative;
      z-index: 1;
    }

    .theme-toggle {
      display: inline-grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 3px;
      width: 100%;
      min-width: 196px;
      border: 1px solid #D8E0EA;
      border-radius: 999px;
      background: rgba(255,255,255,.92);
      padding: 3px;
      box-shadow: 0 8px 18px rgba(21,37,58,.08);
    }

    .theme-toggle-button {
      border: 0;
      border-radius: 999px;
      background: transparent;
      color: #64748B;
      cursor: pointer;
      font: inherit;
      font-size: 12px;
      font-weight: 800;
      line-height: 1;
      padding: 9px 10px;
      transition: background-color .18s ease, color .18s ease, box-shadow .18s ease;
    }

    .theme-toggle-button[aria-pressed="true"] {
      background: #2178BD;
      color: #fff;
      box-shadow: 0 6px 14px rgba(33,120,189,.2);
    }

    .theme-toggle-button:focus-visible {
      outline: 2px solid #FDCD21;
      outline-offset: 2px;
    }

    .profile-menu {
      position: relative;
      width: 100%;
    }

    .profile-menu-button {
      display: inline-flex;
      width: 100%;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      border: 1px solid #D8E0EA;
      border-radius: 8px;
      background: #fff;
      color: #15253A;
      cursor: pointer;
      font: inherit;
      font-size: 14px;
      font-weight: 800;
      padding: 8px 10px;
      text-align: left;
    }

    .profile-menu-avatar {
      display: inline-flex;
      height: 34px;
      width: 34px;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      border-radius: 999px;
      background: #2178BD;
      color: #fff;
      font-size: 14px;
      font-weight: 900;
      text-transform: uppercase;
    }

    .profile-menu-name {
      min-width: 0;
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .profile-menu-chevron {
      color: #64748B;
      font-size: 12px;
      transition: transform .18s ease;
    }

    .profile-menu-button[aria-expanded="true"] .profile-menu-chevron {
      transform: rotate(180deg);
    }

    .profile-menu-panel {
      display: none;
      margin-top: 8px;
      min-width: 230px;
      overflow: hidden;
      border: 1px solid #D8E0EA;
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 16px 32px rgba(21,37,58,.14);
    }

    .profile-menu[data-open="true"] .profile-menu-panel {
      display: block;
    }

    .profile-menu-welcome {
      border-bottom: 1px solid #D8E0EA;
      background: #F5F8FB;
      padding: 12px;
    }

    .profile-menu-welcome span {
      display: block;
      color: #64748B;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
    }

    .profile-menu-welcome strong {
      display: block;
      margin-top: 3px;
      color: #15253A;
      font-size: 14px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .profile-menu-link {
      display: block;
      color: #15253A;
      font-size: 14px;
      font-weight: 700;
      padding: 11px 12px;
      text-decoration: none;
    }

    .profile-menu-link:hover,
    .profile-menu-link:focus-visible {
      background: #EEF7FF;
      color: #2178BD;
      outline: none;
    }

    .profile-menu-link-danger {
      color: #b91c1c;
    }

    @media (min-width: 1024px) {
      .profile-menu {
        width: auto;
      }

      .profile-menu-button {
        width: auto;
        min-width: 158px;
      }

      .profile-menu-panel {
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        z-index: 70;
        margin-top: 0;
      }
    }

    html[data-theme="white"] body,
    html[data-theme="system"] body {
      background: #eef4f8 !important;
    }

    html[data-theme="dark"] body {
      background: #0b1220 !important;
      color: #f8fafc !important;
    }

    html[data-theme="dark"] body::before {
      opacity: 0.034;
      filter: grayscale(1) brightness(1.35);
    }

    html[data-theme="dark"] .theme-toggle {
      border-color: #2f3b52;
      background: rgba(15,23,42,.92);
      box-shadow: 0 8px 18px rgba(0,0,0,.2);
    }

    html[data-theme="dark"] .theme-toggle-button {
      color: #cbd5e1;
    }

    html[data-theme="dark"] .theme-toggle-button[aria-pressed="true"] {
      background: #FDCD21;
      color: #111827;
      box-shadow: 0 6px 14px rgba(253,205,33,.16);
    }

    html[data-theme="dark"] .profile-menu-button,
    html[data-theme="dark"] .profile-menu-panel {
      border-color: #2f3b52;
      background: #111827;
      color: #f8fafc;
    }

    html[data-theme="dark"] .profile-menu-welcome {
      border-color: #2f3b52;
      background: #172033;
    }

    html[data-theme="dark"] .profile-menu-welcome strong,
    html[data-theme="dark"] .profile-menu-link {
      color: #f8fafc;
    }

    html[data-theme="dark"] .profile-menu-link:hover,
    html[data-theme="dark"] .profile-menu-link:focus-visible {
      background: #172033;
      color: #FDCD21;
    }

    html[data-theme="dark"] .bg-white,
    html[data-theme="dark"] header,
    html[data-theme="dark"] section,
    html[data-theme="dark"] aside,
    html[data-theme="dark"] article,
    html[data-theme="dark"] table,
    html[data-theme="dark"] .shadow-soft {
      background-color: #111827 !important;
      color: #f8fafc !important;
      border-color: #263244 !important;
    }

    html[data-theme="dark"] .security-notice {
      background-color: #8B0000 !important;
      color: #fff !important;
      border-color: #450a0a !important;
    }

    html[data-theme="dark"] .security-notice h2,
    html[data-theme="dark"] .security-notice p {
      color: #fff !important;
    }

    html[data-theme="dark"] .bg-brand-soft,
    html[data-theme="dark"] select,
    html[data-theme="dark"] input {
      background-color: #0f172a !important;
      color: #f8fafc !important;
      border-color: #334155 !important;
    }

    html[data-theme="dark"] .text-brand-muted,
    html[data-theme="dark"] .text-slate-700,
    html[data-theme="dark"] .text-sky-900,
    html[data-theme="dark"] .text-amber-950,
    html[data-theme="dark"] .text-emerald-950,
    html[data-theme="dark"] .text-indigo-950 {
      color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .text-brand-ink {
      color: #f8fafc !important;
    }

    html[data-theme="dark"] .bg-brand-yellow,
    html[data-theme="dark"] .bg-\\[\\#FDCD21\\] {
      background-color: #FDCD21 !important;
      color: #0f172a !important;
    }

    html[data-theme="dark"] .bg-\\[\\#EEF7FF\\],
    html[data-theme="dark"] .bg-sky-50,
    html[data-theme="dark"] .bg-amber-50,
    html[data-theme="dark"] .bg-emerald-50,
    html[data-theme="dark"] .bg-indigo-50,
    html[data-theme="dark"] .bg-red-50,
    html[data-theme="dark"] .bg-\\[\\#FFF5C2\\] {
      background-color: #172033 !important;
      color: #f8fafc !important;
      border-color: #2f3b52 !important;
    }

    html[data-theme="dark"] .text-red-800,
    html[data-theme="dark"] .text-emerald-800,
    html[data-theme="dark"] .text-sky-900,
    html[data-theme="dark"] .text-amber-700,
    html[data-theme="dark"] .text-emerald-700,
    html[data-theme="dark"] .text-indigo-700 {
      color: #e2e8f0 !important;
    }

    html[data-theme="dark"] .border-\\[\\#BFD8EF\\],
    html[data-theme="dark"] .border-sky-200,
    html[data-theme="dark"] .border-amber-200,
    html[data-theme="dark"] .border-emerald-200,
    html[data-theme="dark"] .border-indigo-200,
    html[data-theme="dark"] .border-red-200,
    html[data-theme="dark"] .border-red-100 {
      border-color: #334155 !important;
    }

    html[data-theme="dark"] img[src*="alogo_light"] {
      filter: drop-shadow(0 1px 0 rgba(255,255,255,.35));
    }

    html[data-theme="dark"] .voucher-status-available,
    html[data-theme="system"] .voucher-status-available {
      background-color: #86efac !important;
      color: #052e16 !important;
      border: 1px solid #22c55e !important;
    }

    html[data-theme="dark"] .voucher-status-limited,
    html[data-theme="system"] .voucher-status-limited {
      background-color: #fde68a !important;
      color: #451a03 !important;
      border: 1px solid #f59e0b !important;
    }

    html[data-theme="dark"] .badge-activated,
    html[data-theme="system"] .badge-activated {
      background-color: #86efac !important;
      color: #052e16 !important;
      border: 1px solid #22c55e !important;
    }

    html[data-theme="dark"] .badge-redeemed,
    html[data-theme="system"] .badge-redeemed,
    html[data-theme="dark"] .badge-not-redeemed,
    html[data-theme="system"] .badge-not-redeemed {
      background-color: #e2e8f0 !important;
      color: #0f172a !important;
      border: 1px solid #94a3b8 !important;
    }

    html[data-theme="dark"] .badge-pending,
    html[data-theme="system"] .badge-pending {
      background-color: #fde68a !important;
      color: #451a03 !important;
      border: 1px solid #f59e0b !important;
    }

    html[data-theme="dark"] .badge-expired,
    html[data-theme="system"] .badge-expired {
      background-color: #fecaca !important;
      color: #450a0a !important;
      border: 1px solid #ef4444 !important;
    }

    @media (prefers-color-scheme: dark) {
      html[data-theme="system"] body {
        background: #0b1220 !important;
        color: #f8fafc !important;
      }

      html[data-theme="system"] body::before {
        opacity: 0.045;
        filter: grayscale(1) brightness(1.35);
      }

      html[data-theme="system"] .bg-white,
      html[data-theme="system"] header,
      html[data-theme="system"] section,
      html[data-theme="system"] aside,
      html[data-theme="system"] article,
      html[data-theme="system"] table,
      html[data-theme="system"] .shadow-soft {
        background-color: #111827 !important;
        color: #f8fafc !important;
        border-color: #263244 !important;
      }

      html[data-theme="system"] .security-notice {
        background-color: #8B0000 !important;
        color: #fff !important;
        border-color: #450a0a !important;
      }

      html[data-theme="system"] .security-notice h2,
      html[data-theme="system"] .security-notice p {
        color: #fff !important;
      }

      html[data-theme="system"] .bg-brand-soft,
      html[data-theme="system"] select,
      html[data-theme="system"] input {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
      }

      html[data-theme="system"] .text-brand-muted,
      html[data-theme="system"] .text-sky-900,
      html[data-theme="system"] .text-amber-950,
      html[data-theme="system"] .text-emerald-950,
      html[data-theme="system"] .text-indigo-950 {
        color: #cbd5e1 !important;
      }

      html[data-theme="system"] .text-brand-ink {
        color: #f8fafc !important;
      }

      html[data-theme="system"] .text-red-800,
      html[data-theme="system"] .text-emerald-800,
      html[data-theme="system"] .text-sky-900,
      html[data-theme="system"] .text-amber-700,
      html[data-theme="system"] .text-emerald-700,
      html[data-theme="system"] .text-indigo-700 {
        color: #e2e8f0 !important;
      }

      html[data-theme="system"] .bg-brand-yellow,
      html[data-theme="system"] .bg-\\[\\#FDCD21\\] {
        background-color: #FDCD21 !important;
        color: #0f172a !important;
      }

      html[data-theme="system"] .bg-\\[\\#EEF7FF\\],
      html[data-theme="system"] .bg-sky-50,
      html[data-theme="system"] .bg-amber-50,
      html[data-theme="system"] .bg-emerald-50,
      html[data-theme="system"] .bg-indigo-50,
      html[data-theme="system"] .bg-red-50,
      html[data-theme="system"] .bg-\\[\\#FFF5C2\\] {
        background-color: #172033 !important;
        color: #f8fafc !important;
        border-color: #2f3b52 !important;
      }

      html[data-theme="system"] .theme-toggle {
        border-color: #2f3b52;
        background: rgba(15,23,42,.92);
        box-shadow: 0 8px 18px rgba(0,0,0,.2);
      }

      html[data-theme="system"] .theme-toggle-button {
        color: #cbd5e1;
      }

      html[data-theme="system"] .theme-toggle-button[aria-pressed="true"] {
        background: #FDCD21;
        color: #111827;
        box-shadow: 0 6px 14px rgba(253,205,33,.16);
      }

      html[data-theme="system"] .profile-menu-button,
      html[data-theme="system"] .profile-menu-panel {
        border-color: #2f3b52;
        background: #111827;
        color: #f8fafc;
      }

      html[data-theme="system"] .profile-menu-welcome {
        border-color: #2f3b52;
        background: #172033;
      }

      html[data-theme="system"] .profile-menu-welcome strong,
      html[data-theme="system"] .profile-menu-link {
        color: #f8fafc;
      }

      html[data-theme="system"] .profile-menu-link:hover,
      html[data-theme="system"] .profile-menu-link:focus-visible {
        background: #172033;
        color: #FDCD21;
      }
    }
  `;
  document.head.appendChild(style);
}

function applyTheme(theme) {
  const selectedTheme = ["system", "white", "dark"].includes(theme) ? theme : "system";
  document.documentElement.dataset.theme = selectedTheme;
  localStorage.setItem("staroil:theme", selectedTheme);
  document.querySelectorAll("[data-theme-select]").forEach((select) => {
    select.value = selectedTheme;
  });
  document.querySelectorAll("[data-theme-option]").forEach((button) => {
    const active = button.dataset.themeOption === selectedTheme;
    button.setAttribute("aria-pressed", String(active));
  });
}

function enhanceThemeSelect(select) {
  if (select.dataset.themeEnhanced === "true") return;
  select.dataset.themeEnhanced = "true";

  const toggle = document.createElement("div");
  toggle.className = "theme-toggle";
  toggle.setAttribute("role", "group");
  toggle.setAttribute("aria-label", "Theme");

  [
    ["system", "System"],
    ["white", "White"],
    ["dark", "Dark"]
  ].forEach(([value, label]) => {
    const button = document.createElement("button");
    button.type = "button";
    button.className = "theme-toggle-button";
    button.dataset.themeOption = value;
    button.textContent = label;
    button.setAttribute("aria-pressed", "false");
    button.addEventListener("click", () => applyTheme(value));
    toggle.appendChild(button);
  });

  select.classList.add("sr-only");
  select.setAttribute("tabindex", "-1");
  select.insertAdjacentElement("afterend", toggle);
}

function bindThemeControls() {
  document.querySelectorAll("[data-theme-select]").forEach((select) => {
    enhanceThemeSelect(select);
    if (select.dataset.themeBound === "true") return;
    select.dataset.themeBound = "true";
    select.addEventListener("change", () => applyTheme(select.value));
  });
  applyTheme(localStorage.getItem("staroil:theme") || "system");
}

function displayName() {
  return (authState.name || "User").trim() || "User";
}

function displayInitials() {
  const parts = displayName().split(/\s+/).filter(Boolean);
  const initials = parts.slice(0, 2).map((part) => part[0]).join("");
  return (initials || "U").toUpperCase();
}

function getStoredProfileAvatar() {
  try {
    const avatar = JSON.parse(localStorage.getItem("staroil:profileAvatar") || "null");
    if (!avatar || typeof avatar !== "object" || !avatar.text) return null;
    return {
      text: avatar.text,
      bg: avatar.bg || "#2178BD",
      color: avatar.color || "#FFFFFF"
    };
  } catch (error) {
    return null;
  }
}

function enhanceProfileDropdown() {
  document.querySelectorAll("[data-menu]").forEach((menu) => {
    if (!menu.querySelector("[data-wallet-nav-link]")) {
      const cartLink = Array.from(menu.querySelectorAll('a[href="cart"]')).find((link) => link.hasAttribute("data-auth-only"));
      const themeLabel = menu.querySelector("[data-theme-select]")?.closest("label");
      const walletLink = document.createElement("a");
      walletLink.href = "wallet";
      walletLink.dataset.authOnly = "";
      walletLink.dataset.walletNavLink = "true";
      walletLink.className = "hidden block w-full rounded-ui border border-brand-line px-3 py-2 text-sm font-semibold text-brand-ink hover:border-brand-blue hover:text-brand-blue lg:w-auto";
      walletLink.innerHTML = `<span class="inline-flex items-center gap-2"><span aria-hidden="true">₵</span><span>Wallet</span></span>`;
      menu.insertBefore(walletLink, themeLabel || cartLink?.nextSibling || null);
    }

    if (menu.querySelector("[data-profile-dropdown]")) return;

    const voucherLink = Array.from(menu.querySelectorAll('a[href="vouchers"]')).find((link) => link.hasAttribute("data-auth-only"));
    const profileLink = Array.from(menu.querySelectorAll('a[href="profile"]')).find((link) => link.hasAttribute("data-auth-only"));
    const logoutLink = Array.from(menu.querySelectorAll('a[href="logout"]')).find((link) => link.hasAttribute("data-auth-only"));
    const welcome = menu.querySelector("[data-user-welcome]");

    const dropdown = document.createElement("div");
    dropdown.className = "profile-menu hidden";
    dropdown.dataset.profileDropdown = "true";
    dropdown.setAttribute("data-auth-only", "");
    dropdown.innerHTML = `
      <button class="profile-menu-button" type="button" data-profile-menu-button aria-expanded="false" aria-haspopup="true">
        <span class="profile-menu-avatar" data-profile-avatar>U</span>
        <span class="profile-menu-name" data-profile-menu-name>Profile</span>
        <span class="profile-menu-chevron" aria-hidden="true">v</span>
      </button>
      <div class="profile-menu-panel" data-profile-menu-panel role="menu">
        <div class="profile-menu-welcome">
          <span>Welcome</span>
          <strong data-profile-menu-welcome>User</strong>
        </div>
        <a class="profile-menu-link" href="vouchers" role="menuitem">My Vouchers</a>
        <a class="profile-menu-link" href="wallet" role="menuitem">Wallet</a>
        <a class="profile-menu-link" href="analytics" role="menuitem">Analytics</a>
        <a class="profile-menu-link" href="profile" role="menuitem">My Profile</a>
        <a class="profile-menu-link profile-menu-link-danger" href="logout" role="menuitem">Logout</a>
      </div>
    `;

    const insertBefore = logoutLink || menu.querySelector("[data-guest-only]") || null;
    menu.insertBefore(dropdown, insertBefore);

    [voucherLink, profileLink, logoutLink, welcome].forEach((node) => {
      if (node) node.remove();
    });

    const button = dropdown.querySelector("[data-profile-menu-button]");
    button?.addEventListener("click", (event) => {
      event.stopPropagation();
      const open = dropdown.dataset.open === "true";
      dropdown.dataset.open = String(!open);
      button.setAttribute("aria-expanded", String(!open));
    });
  });
}

function syncProfileDropdown() {
  const avatar = getStoredProfileAvatar();
  document.querySelectorAll("[data-profile-dropdown]").forEach((dropdown) => {
    const avatarNode = dropdown.querySelector("[data-profile-avatar]");
    if (avatar) {
      avatarNode.textContent = avatar.text;
      avatarNode.style.backgroundColor = avatar.bg;
      avatarNode.style.color = avatar.color;
    } else {
      avatarNode.textContent = displayInitials();
      avatarNode.style.backgroundColor = "#2178BD";
      avatarNode.style.color = "#FFFFFF";
    }
    dropdown.querySelector("[data-profile-menu-name]").textContent = displayName();
    dropdown.querySelector("[data-profile-menu-welcome]").textContent = displayName();
  });
}

function closeProfileDropdowns() {
  document.querySelectorAll("[data-profile-dropdown]").forEach((dropdown) => {
    dropdown.dataset.open = "false";
    dropdown.querySelector("[data-profile-menu-button]")?.setAttribute("aria-expanded", "false");
  });
}

function ensurePwaMeta() {
  if (!document.querySelector('link[rel="manifest"]')) {
    const manifest = document.createElement("link");
    manifest.rel = "manifest";
    manifest.href = "pwa/manifest.json";
    document.head.appendChild(manifest);
  }

  if (!document.querySelector('meta[name="theme-color"]')) {
    const theme = document.createElement("meta");
    theme.name = "theme-color";
    theme.content = "#2178BD";
    document.head.appendChild(theme);
  }
}

async function registerPwa() {
  ensurePwaMeta();
  if (!("serviceWorker" in navigator)) return;

  try {
    const registration = await navigator.serviceWorker.register("sw.js");
    registration.update();
    navigator.serviceWorker.addEventListener("controllerchange", () => {
      if (sessionStorage.getItem("staroil:swReloaded") === "true") return;
      sessionStorage.setItem("staroil:swReloaded", "true");
      location.reload();
    });
  } catch (error) {
    // The app remains usable without service worker support.
  }
}

function postJson(endpoint, payload) {
  const body = JSON.stringify(payload);
  if (navigator.sendBeacon) {
    const blob = new Blob([body], { type: "application/json" });
    if (navigator.sendBeacon(endpoint, blob)) return;
  }

  fetch(endpoint, {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body,
    keepalive: true
  }).catch(() => {});
}

function getActivityVisitorId() {
  let id = localStorage.getItem("staroil:visitorId");
  if (!id) {
    id = (window.crypto?.randomUUID ? window.crypto.randomUUID() : `visitor-${Date.now()}-${Math.random().toString(16).slice(2)}`).replace(/[^a-zA-Z0-9_-]/g, "");
    localStorage.setItem("staroil:visitorId", id);
  }
  return id;
}

function getActivitySessionId() {
  let id = sessionStorage.getItem("staroil:activitySessionId");
  if (!id) {
    id = (window.crypto?.randomUUID ? window.crypto.randomUUID() : `session-${Date.now()}-${Math.random().toString(16).slice(2)}`).replace(/[^a-zA-Z0-9_-]/g, "");
    sessionStorage.setItem("staroil:activitySessionId", id);
  }
  return id;
}

const activityPageId = (window.crypto?.randomUUID ? window.crypto.randomUUID() : `page-${Date.now()}-${Math.random().toString(16).slice(2)}`).replace(/[^a-zA-Z0-9_-]/g, "");

function activityRoute() {
  return normalizeRoute(location.pathname.split("/").pop() || "index");
}

function activityPath() {
  return `${location.pathname}${location.search ? "?filtered" : ""}`;
}

function safeActivityText(value, max = 80) {
  return String(value || "")
    .replace(/\s+/g, " ")
    .replace(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/gi, "[email]")
    .replace(/\b(?:\+?\d[\d\s-]{6,}\d)\b/g, "[number]")
    .trim()
    .slice(0, max);
}

function describeActivityTarget(target) {
  const el = target?.closest?.("button,a,input,select,textarea,label,[role='button'],[data-nav],[data-payment],[data-add],[data-remove],[data-qty]") || target;
  if (!el || el === document || el === window) {
    return { target: "document", targetText: "", targetRole: "" };
  }

  const tag = (el.tagName || "element").toLowerCase();
  const id = el.id ? `#${el.id}` : "";
  const dataKey = el.dataset?.payment || el.dataset?.add || el.dataset?.remove || el.dataset?.qty || el.dataset?.nav || "";
  const role = el.getAttribute?.("role") || el.type || el.getAttribute?.("aria-label") || "";
  const text = tag === "input" || tag === "textarea"
    ? (el.name || el.id || el.type || "field")
    : (el.getAttribute?.("aria-label") || el.innerText || el.textContent || el.href || "");

  return {
    target: safeActivityText(`${tag}${id}${dataKey ? `[${dataKey}]` : ""}`, 120),
    targetText: safeActivityText(text, 120),
    targetRole: safeActivityText(role, 60)
  };
}

function activityMetrics(extra = {}) {
  return {
    elapsed_ms: Date.now() - activityTracker.startedAt,
    active_ms: Math.round(activityTracker.activeMs),
    max_scroll_percent: activityTracker.maxScroll,
    viewport_width: window.innerWidth,
    viewport_height: window.innerHeight,
    ...extra
  };
}

function markActivity() {
  const now = Date.now();
  const gap = now - activityTracker.lastActiveAt;
  if (gap > 0 && gap < 30000 && document.visibilityState === "visible") {
    activityTracker.activeMs += gap;
  }
  activityTracker.lastActiveAt = now;
}

function queueActivity(type, detail = {}, immediate = false) {
  if (!activityTracker.initialized && type !== "page_view") return;
  markActivity();
  activityTracker.queue.push({
    type,
    clientTime: new Date().toISOString(),
    route: activityRoute(),
    path: activityPath(),
    ...detail
  });

  if (activityTracker.queue.length >= 20 || immediate) {
    flushActivity();
    return;
  }

  clearTimeout(activityTracker.flushTimer);
  activityTracker.flushTimer = setTimeout(flushActivity, 7000);
}

function flushActivity() {
  if (!activityTracker.queue.length) return;
  const events = activityTracker.queue.splice(0, 60);
  postJson(activityTracker.endpoint, {
    visitorId: getActivityVisitorId(),
    activitySessionId: getActivitySessionId(),
    pageId: activityPageId,
    events
  });
}

function updateScrollActivity() {
  const doc = document.documentElement;
  const scrollTop = window.scrollY || doc.scrollTop || 0;
  const scrollable = Math.max(1, doc.scrollHeight - window.innerHeight);
  const percent = Math.min(100, Math.max(0, Math.round((scrollTop / scrollable) * 100)));
  activityTracker.maxScroll = Math.max(activityTracker.maxScroll, percent);
  const now = Date.now();
  if (now - activityTracker.lastScrollAt > 2500) {
    activityTracker.lastScrollAt = now;
    queueActivity("scroll", { metrics: activityMetrics({ scroll_percent: percent }) });
  }
}

function initializeUserActivityTracking() {
  if (activityTracker.initialized) return;
  activityTracker.initialized = true;

  queueActivity("page_view", {
    metrics: activityMetrics({
      referrer_present: Boolean(document.referrer),
      cart_items: count()
    })
  }, true);

  activityTracker.heartbeatTimer = setInterval(() => {
    queueActivity("page_heartbeat", {
      metrics: activityMetrics({
        cart_items: count()
      })
    }, true);
  }, 30000);

  document.addEventListener("click", (event) => {
    const target = describeActivityTarget(event.target);
    queueActivity("click", {
      ...target,
      metrics: activityMetrics({
        x: event.clientX,
        y: event.clientY
      })
    });
  }, true);

  document.addEventListener("submit", (event) => {
    const target = describeActivityTarget(event.target);
    queueActivity("form_submit", {
      ...target,
      metrics: activityMetrics({
        method: event.target?.method || "GET"
      })
    }, true);
  }, true);

  window.addEventListener("scroll", updateScrollActivity, { passive: true });
  window.addEventListener("mousemove", (event) => {
    activityTracker.lastMouse = { x: event.clientX, y: event.clientY };
    markActivity();
  }, { passive: true });
  window.addEventListener("keydown", markActivity, { passive: true });
  window.addEventListener("touchstart", markActivity, { passive: true });

  document.addEventListener("dragstart", (event) => {
    activityTracker.drag = { startedAt: Date.now(), target: describeActivityTarget(event.target) };
    queueActivity("drag_start", {
      ...activityTracker.drag.target,
      metrics: activityMetrics(activityTracker.lastMouse)
    });
  }, true);

  document.addEventListener("dragend", () => {
    const duration = activityTracker.drag ? Date.now() - activityTracker.drag.startedAt : 0;
    queueActivity("drag_end", {
      ...(activityTracker.drag?.target || {}),
      metrics: activityMetrics({ duration_ms: duration, ...activityTracker.lastMouse })
    }, true);
    activityTracker.drag = null;
  }, true);

  document.addEventListener("visibilitychange", () => {
    queueActivity("visibility_change", {
      metrics: activityMetrics({ hidden: document.visibilityState === "hidden" })
    }, document.visibilityState === "hidden");
    if (document.visibilityState === "hidden") flushActivity();
  });

  window.addEventListener("pagehide", () => {
    queueActivity("page_leave", {
      metrics: activityMetrics({
        total_time_ms: Date.now() - activityTracker.startedAt,
        active_ms: Math.round(activityTracker.activeMs),
        max_scroll_percent: activityTracker.maxScroll,
        cart_items: count()
      })
    }, true);
    flushActivity();
  });

  window.addEventListener("error", (event) => {
    queueActivity("error", {
      targetText: safeActivityText(event.message, 120),
      metrics: activityMetrics({ line: event.lineno || 0 })
    }, true);
  });
}

function trackCartEvent(event = "cart_update", items = cart()) {
  clearTimeout(cartTrackTimer);
  cartTrackTimer = setTimeout(() => {
    const cartItems = Array.isArray(items) ? items : cart();
    const totalQuantity = cartItems.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
    const currentSubtotal = cartItems.reduce((sum, item) => sum + Number(item.amount || 0) * Number(item.quantity || 0), 0);
    postJson("abandoned_cart_track", {
      event,
      cartId: getCartId(),
      route: location.pathname.replace(/^.*\//, "") || "index",
      subtotal: currentSubtotal,
      totalQuantity,
      items: cartItems.map((item) => ({
        id: item.id,
        amount: item.amount,
        quantity: item.quantity,
        status: item.status,
        image: item.image,
        stock: item.stock
      }))
    });
  }, ["checkout_started", "checkout_success", "cart_page_left"].includes(event) ? 0 : 450);
}

function initializeAbandonedCartTracking() {
  if (cart().length) {
    trackCartEvent("cart_seen", cart());
  }

  document.addEventListener("visibilitychange", () => {
    const route = normalizeRoute(location.pathname.split("/").pop() || "index");
    if (document.visibilityState === "hidden" && route === "cart" && cart().length) {
      trackCartEvent("cart_page_left", cart());
    }
  });
}

function clearCartAfterPaymentReturn() {
  const route = normalizeRoute(location.pathname.split("/").pop() || "index");
  if (!["success", "success_hubtel", "success_wallet"].includes(route) || !cart().length) return;
  trackCartEvent("checkout_success", cart());
  saveCart([], "cart_cleared_after_payment");
}

function toast(type, title, message) {
  const region = document.getElementById("toast-region");
  if (!region) return;
  const styles = {
    success: ["bg-emerald-600", "text-emerald-900", "✓"],
    warning: ["bg-amber-500", "text-amber-900", "!"],
    error: ["bg-red-600", "text-red-900", "×"],
    info: ["bg-[#2178BD]", "text-sky-950", "i"]
  };
  const [iconBg, titleColor, icon] = styles[type] || styles.info;
  const node = document.createElement("div");
  node.className = "rounded-ui border border-brand-line bg-white p-4 shadow-soft";
  node.innerHTML = `
    <div class="flex gap-3">
      <span class="${iconBg} flex h-7 w-7 shrink-0 items-center justify-center rounded-ui text-sm font-bold text-white">${icon}</span>
      <div class="min-w-0 flex-1">
        <p class="${titleColor} text-sm font-bold">${title}</p>
        <p class="mt-1 text-sm leading-5 text-brand-muted">${message}</p>
      </div>
      <button class="rounded-ui p-1 text-brand-muted hover:bg-brand-soft" type="button" aria-label="Dismiss notification">×</button>
    </div>`;
  node.querySelector("button").addEventListener("click", () => node.remove());
  region.appendChild(node);
  setTimeout(() => node.remove(), 4800);
}

function updateShell() {
  enhanceProfileDropdown();
  document.querySelectorAll("[data-cart-count]").forEach((el) => el.textContent = count());
  document.querySelectorAll("[data-auth-only]").forEach((el) => {
    el.classList.toggle("hidden", !authState.loggedIn);
  });
  document.querySelectorAll("[data-guest-only]").forEach((el) => {
    el.classList.toggle("hidden", authState.loggedIn);
  });
  document.querySelectorAll("[data-user-welcome]").forEach((el) => {
    const name = authState.name ? `, ${authState.name}` : "";
    el.textContent = `Welcome${name}`;
  });
  syncProfileDropdown();
  const path = normalizeRoute(location.pathname.split("/").pop() || "index");
  document.querySelectorAll("[data-nav]").forEach((link) => {
    const active = normalizeRoute(link.getAttribute("href")) === path;
    link.classList.toggle("bg-brand-blue", active);
    link.classList.toggle("text-white", active);
    link.classList.toggle("text-brand-muted", !active);
  });
}

function showMfaSetupModal() {
  if (!authState.loggedIn || !authState.showMfaSetupReminder) return;
  if (document.getElementById("mfa-setup-reminder-modal")) return;

  const modal = document.createElement("div");
  modal.id = "mfa-setup-reminder-modal";
  modal.className = "fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/60 px-4 py-6";
  modal.setAttribute("role", "dialog");
  modal.setAttribute("aria-modal", "true");
  modal.setAttribute("aria-labelledby", "mfa-setup-reminder-title");
  modal.innerHTML = `
    <div class="w-full max-w-md rounded-ui border border-brand-line bg-white p-5 shadow-soft">
      <div class="flex items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-ui bg-brand-yellow text-lg font-bold text-brand-ink">!</span>
        <div class="min-w-0">
          <h2 id="mfa-setup-reminder-title" class="text-lg font-bold text-brand-ink">Secure your account</h2>
          <p class="mt-2 text-sm leading-6 text-brand-muted">You have not set up Mobile OTP or Google Authenticator yet. Add one from your profile to improve voucher purchase and account security.</p>
        </div>
      </div>
      <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
        <button class="rounded-ui border border-brand-line bg-white px-4 py-2.5 text-sm font-semibold text-brand-ink hover:bg-brand-soft focus:outline-none focus:ring-2 focus:ring-brand-blue" type="button" data-mfa-reminder-close>Later</button>
        <a class="rounded-ui bg-brand-blue px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-[#1A659F] focus:outline-none focus:ring-2 focus:ring-brand-blue" href="profile">Set up now</a>
      </div>
    </div>`;

  document.body.appendChild(modal);
  modal.querySelector("[data-mfa-reminder-close]")?.addEventListener("click", () => modal.remove());
  modal.addEventListener("click", (event) => {
    if (event.target === modal) modal.remove();
  });
  modal.querySelector("[data-mfa-reminder-close]")?.focus({ preventScroll: true });
}

async function loadAuthStatus() {
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 3500);

  try {
    const response = await fetch("auth_status", {
      headers: { Accept: "application/json" },
      signal: controller.signal
    });

    if (!response.ok) throw new Error(`Auth status request failed with ${response.status}`);

    const payload = await response.json();
    authState.loggedIn = Boolean(payload.loggedIn);
    authState.name = payload.name || "";
    authState.otpStatus = Number(payload.otpStatus || 0);
    authState.authenticatorStatus = Number(payload.authenticatorStatus || 0);
    authState.showMfaSetupReminder = Boolean(payload.showMfaSetupReminder);
    authState.appCode = payload.appCode || "";
    authState.partnerFee = Math.max(0, Number(payload.partnerFee || 0));
  } catch (error) {
    authState.loggedIn = false;
    authState.name = "";
    authState.otpStatus = 0;
    authState.authenticatorStatus = 0;
    authState.showMfaSetupReminder = false;
    authState.appCode = "";
    authState.partnerFee = 0;
  } finally {
    clearTimeout(timeout);
  }
}

function normalizeRoute(route) {
  const cleaned = (route || "index").replace(/^\.?\//, "").replace(/\.(php|html)$/i, "");
  return cleaned || "index";
}

function imageForAmount(amount) {
  const fallback = fallbackProducts.find((item) => item.amount === Number(amount));
  return fallback ? fallback.image : "images/50cedisvoucher.png";
}

function normalizeVoucherImage(image, amount) {
  if (!image) return imageForAmount(amount);
  const imageValue = String(image);
  if (/^(https?:)?\/\//i.test(imageValue) || imageValue.startsWith("data:")) return imageValue;
  const fileName = imageValue.split(/[\\/]/).pop();
  return fileName ? `images/${fileName}` : imageForAmount(amount);
}

function normalizeSavedCartImages() {
  const items = cart();
  let changed = false;
  const normalized = items.map((item) => {
    const image = normalizeVoucherImage(item.image, item.amount);
    if (image !== item.image) changed = true;
    return { ...item, image };
  });
  if (changed) {
    localStorage.setItem("staroil:cart", JSON.stringify(normalized));
  }
}

function normalizeVoucherStatus(status, stock) {
  const value = String(status ?? "").trim().toLowerCase();

  if (value === "1" || value === "active" || value === "available") return "Available";
  if (value === "0" || value === "inactive" || value === "unavailable") return "Unavailable";
  if (value === "2" || value === "limited") return "Limited";
  if (value === "3" || value === "corporate") return "Corporate";

  return stock > 0 ? "Available" : "Unavailable";
}

async function loadProducts() {
  try {
    const response = await fetch("fetch_vouchers", {
      headers: { Accept: "application/json" }
    });

    if (!response.ok) throw new Error(`Voucher request failed with ${response.status}`);

    const payload = await response.json();
    if (payload.status !== "success" || !Array.isArray(payload.data)) {
      throw new Error("Voucher response was not successful");
    }

    products = payload.data.map((row) => {
      const amount = Number(row.deno);
      const stock = Number(row.available_vouchers || 0);
      return {
        id: `fuel-${row.id || amount}`,
        amount,
        status: normalizeVoucherStatus(row.status, stock),
        stock,
        image: normalizeVoucherImage(row.images, amount),
        loggedIn: Boolean(row.loggedIn)
      };
    }).filter((item) => Number.isFinite(item.amount));
    authState.loggedIn = authState.loggedIn || products.some((item) => item.loggedIn);
  } catch (error) {
    products = fallbackProducts.map((item) => ({ ...item, loggedIn: authState.loggedIn }));
    toast("warning", "Using local voucher list", "The live voucher endpoint was unavailable, so the store is showing local voucher previews.");
  }
}

function productForAmount(amount) {
  const numericAmount = Number(amount);
  return products.find((item) => Number(item.amount) === numericAmount)
    || fallbackProducts.find((item) => Number(item.amount) === numericAmount)
    || {
      id: `fuel-${numericAmount}`,
      amount: numericAmount,
      status: "Available",
      stock: 0,
      image: imageForAmount(numericAmount)
    };
}

async function loadRecommendations(context = "store") {
  const cartAmounts = cart().map((item) => item.amount).filter(Boolean).join(",");
  const url = new URL("voucher_recommendations", window.location.href);
  url.searchParams.set("context", context);
  if (cartAmounts) url.searchParams.set("cart", cartAmounts);

  const response = await fetch(url.toString(), {
    headers: { Accept: "application/json" },
    cache: "no-store"
  });
  if (!response.ok) throw new Error(`Recommendation request failed with ${response.status}`);
  const payload = await response.json();
  if (payload.status !== "success") throw new Error(payload.message || "Recommendations unavailable");
  return payload;
}

function recommendationButton(amount, compact = false) {
  if (!authState.loggedIn) {
    return `<a class="${compact ? "px-3 py-1.5 text-xs" : "px-4 py-2.5 text-sm"} inline-flex justify-center rounded-ui bg-brand-yellow font-bold text-brand-ink hover:bg-[#E8BB1E]" href="signin">Sign in</a>`;
  }
  return `<button class="${compact ? "px-3 py-1.5 text-xs" : "px-4 py-2.5 text-sm"} inline-flex justify-center rounded-ui bg-brand-blue font-semibold text-white hover:bg-[#1A659F]" data-recommendation-add="${Number(amount)}" type="button">Add</button>`;
}

function recommendationCard(item) {
  return `
    <article class="rounded-ui border border-brand-line bg-white p-3 shadow-sm">
      <div class="overflow-hidden rounded-ui border border-brand-line bg-brand-soft">
        <img class="aspect-[3.12/1] w-full object-cover" src="${escapeHtml(item.image || imageForAmount(item.amount))}" alt="Star Oil ${escapeHtml(item.label || money(Number(item.amount || 0)))} voucher" loading="lazy" />
      </div>
      <div class="mt-3 flex items-start justify-between gap-3">
        <div>
          <p class="text-base font-bold">${escapeHtml(item.label || money(Number(item.amount || 0)))}</p>
          <p class="mt-1 text-xs leading-5 text-brand-muted">${escapeHtml(item.reason || "Recommended voucher")}</p>
        </div>
        <span class="rounded-full bg-brand-yellow px-2 py-1 text-[11px] font-bold text-brand-ink">${escapeHtml(item.tag || "Pick")}</span>
      </div>
      <div class="mt-3">${recommendationButton(item.amount)}</div>
    </article>`;
}

function mobileRecommendationSlide(item) {
  const amount = Number(item.amount || 0);
  const label = item.label || money(amount);
  const reason = item.reason || (Number(item.purchased_today || 0) > 0
    ? `Purchased ${Number(item.purchased_today).toLocaleString("en-GH")} time${Number(item.purchased_today) === 1 ? "" : "s"} today`
    : "Popular with voucher customers");
  return `
    <article class="min-w-[78%] snap-start rounded-ui border border-brand-line bg-white p-3 shadow-sm sm:min-w-[340px]">
      <div class="flex gap-3">
        <img class="h-16 w-28 shrink-0 rounded-ui border border-brand-line object-cover" src="${escapeHtml(item.image || imageForAmount(amount))}" alt="Star Oil ${escapeHtml(label)} voucher" loading="lazy" />
        <div class="min-w-0 flex-1">
          <div class="mb-1 flex items-start justify-between gap-2">
            <p class="truncate text-base font-bold">${escapeHtml(label)}</p>
            <span class="shrink-0 rounded-full bg-[#EEF7FF] px-2 py-0.5 text-[11px] font-bold text-brand-blue">${escapeHtml(item.tag || "Pick")}</span>
          </div>
          <p class="line-clamp-2 text-xs leading-5 text-brand-muted">${escapeHtml(reason)}</p>
        </div>
      </div>
      <div class="mt-3">${recommendationButton(amount, true)}</div>
    </article>`;
}

function popularVoucherRow(item) {
  const purchased = Number(item.purchased_today || 0);
  const interest = Number(item.interest || 0);
  const purchaseText = `Purchased ${purchased.toLocaleString("en-GH")} time${purchased === 1 ? "" : "s"} today · ${interest.toLocaleString("en-GH")} interest signal${interest === 1 ? "" : "s"}`;
  return `
    <article class="flex gap-3 rounded-ui border border-brand-line bg-white p-2.5">
      <img class="h-14 w-24 shrink-0 rounded-ui border border-brand-line object-cover" src="${escapeHtml(item.image || imageForAmount(item.amount))}" alt="${escapeHtml(item.label)} voucher" loading="lazy" />
      <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-2">
          <p class="font-bold">${escapeHtml(item.label)}</p>
          <span class="shrink-0 rounded-full bg-[#EEF7FF] px-2 py-0.5 text-[11px] font-bold text-brand-blue">${escapeHtml(item.tag || "Popular")}</span>
        </div>
        <p class="mt-1 text-xs leading-5 text-brand-muted">${purchaseText}</p>
        <div class="mt-2">${recommendationButton(item.amount, true)}</div>
      </div>
    </article>`;
}

function recentPurchaseRow(item) {
  return `
    <article class="flex items-center gap-3 rounded-ui border border-brand-line bg-white p-2.5">
      <img class="h-10 w-16 shrink-0 rounded-ui border border-brand-line object-cover" src="${escapeHtml(item.image || imageForAmount(item.amount))}" alt="Recent voucher purchase" loading="lazy" />
      <div class="min-w-0">
        <p class="truncate text-sm font-bold">${escapeHtml(item.message || "A customer bought a voucher")}</p>
        <p class="mt-0.5 text-xs font-semibold text-brand-muted">${escapeHtml(item.time_ago || "recently")}</p>
      </div>
    </article>`;
}

function bindRecommendationAdds(root = document) {
  root.querySelectorAll("[data-recommendation-add]").forEach((button) => {
    if (button.dataset.recommendationBound === "true") return;
    button.dataset.recommendationBound = "true";
    button.addEventListener("click", () => addRecommendationToCart(Number(button.dataset.recommendationAdd)));
  });
}

function addRecommendationToCart(amount) {
  if (!authState.loggedIn) {
    location.href = "signin";
    return;
  }

  const product = productForAmount(amount);
  const items = cart();
  const existing = items.find((item) => Number(item.amount) === Number(amount));
  if (existing) existing.quantity += 1;
  else items.push({ ...product, id: product.id || `fuel-${amount}`, quantity: 1 });
  saveCart(items, "recommendation_added");
  renderCart();
  updateShell();
  fillTotals();
  toast("success", "Recommended voucher added", `${money(Number(amount))} fuel voucher was added to your cart.`);
}

async function renderRecommendations() {
  const panels = document.querySelectorAll("[data-mobile-recommendations-panel], [data-recommendations-panel], [data-popular-vouchers-panel], [data-recent-purchases-panel]");
  if (!panels.length) return;

  panels.forEach((panel) => {
    const target = panel.querySelector("[data-mobile-recommendations-list], [data-recommendations-list], [data-popular-vouchers-list], [data-recent-purchases-list]");
    if (target) target.innerHTML = loadingSpinner("Loading recommendations...");
  });

  try {
    const context = document.querySelector("[data-mobile-recommendations-panel], [data-recommendations-panel]")?.dataset.recommendationContext || "store";
    const payload = await loadRecommendations(context);
    const recommendationItems = Array.isArray(payload.recommendations) ? payload.recommendations : [];
    const popularItems = Array.isArray(payload.popular) ? payload.popular : [];

    document.querySelectorAll("[data-mobile-recommendations-list]").forEach((list) => {
      const items = recommendationItems.length ? recommendationItems : popularItems.slice(0, 4).map((item) => ({
        ...item,
        reason: Number(item.purchased_today || 0) > 0
          ? `Purchased ${Number(item.purchased_today).toLocaleString("en-GH")} time${Number(item.purchased_today) === 1 ? "" : "s"} today`
          : `${Number(item.interest || 0).toLocaleString("en-GH")} customer interest signal${Number(item.interest || 0) === 1 ? "" : "s"}`
      }));
      list.innerHTML = items.length
        ? items.map(mobileRecommendationSlide).join("")
        : `<div class="min-w-full rounded-ui border border-brand-line bg-white p-4 text-sm font-semibold text-brand-muted">Recommendations will appear after customers interact with vouchers.</div>`;
    });

    document.querySelectorAll("[data-recommendations-list]").forEach((list) => {
      const items = recommendationItems;
      list.innerHTML = items.length
        ? items.map(recommendationCard).join("")
        : `<div class="rounded-ui border border-brand-line bg-brand-soft p-4 text-sm font-semibold text-brand-muted">No recommendation data is available yet.</div>`;
    });

    document.querySelectorAll("[data-popular-vouchers-list]").forEach((list) => {
      const items = popularItems.slice(0, 4);
      list.innerHTML = items.length
        ? items.map(popularVoucherRow).join("")
        : `<p class="text-sm text-brand-muted">Popular voucher data will appear after customers interact with the store.</p>`;
    });

    document.querySelectorAll("[data-recent-purchases-list]").forEach((list) => {
      const items = Array.isArray(payload.recent_purchases) ? payload.recent_purchases.slice(0, 5) : [];
      list.innerHTML = items.length
        ? items.map(recentPurchaseRow).join("")
        : `<p class="text-sm text-brand-muted">Recent anonymous purchases will appear after completed payments are logged.</p>`;
    });

    bindRecommendationAdds(document);
  } catch (error) {
    panels.forEach((panel) => {
      const target = panel.querySelector("[data-mobile-recommendations-list], [data-recommendations-list], [data-popular-vouchers-list], [data-recent-purchases-list]");
      if (target) target.innerHTML = `<p class="text-sm font-semibold text-brand-muted">Recommendations could not be loaded right now.</p>`;
    });
  }
}

function addToCart(id) {
  if (!authState.loggedIn) {
    location.href = "signin";
    return;
  }

  const product = products.find((item) => item.id === id);
  if (!product) return;
  const items = cart();
  const existing = items.find((item) => item.id === id);
  if (existing) existing.quantity += 1;
  else items.push({ ...product, quantity: 1 });
  saveCart(items, "cart_item_added");
  updateShell();
  fillTotals();
  toast("success", "Voucher added", `${money(product.amount)} fuel voucher was added to your cart.`);
}

function changeQty(id, delta) {
  let items = cart();
  const item = items.find((entry) => entry.id === id);
  if (!item) return;
  item.quantity += delta;
  if (item.quantity <= 0) items = items.filter((entry) => entry.id !== id);
  saveCart(items, "cart_quantity_changed");
  renderCart();
  updateShell();
  fillTotals();
}

function removeItem(id) {
  saveCart(cart().filter((item) => item.id !== id), "cart_item_removed");
  renderCart();
  updateShell();
  fillTotals();
  toast("warning", "Voucher removed", "The selected voucher was removed from your cart.");
}

async function renderStore() {
  const grid = document.getElementById("voucher-grid");
  if (!grid) return;
  grid.innerHTML = `
    <div class="rounded-ui border border-brand-line bg-white p-6 shadow-soft sm:col-span-2 xl:col-span-3">
      ${loadingSpinner("Loading vouchers...")}
    </div>`;
  await loadProducts();
  grid.innerHTML = products.map((item) => `
    <article class="rounded-ui border border-brand-line bg-white p-4 shadow-soft">
      <div class="overflow-hidden rounded-ui border border-brand-line bg-brand-soft">
        <img class="aspect-[3.12/1] w-full object-cover" src="${item.image}" alt="Star Oil ${money(item.amount)} fuel voucher preview" loading="lazy" />
      </div>
      <div class="mt-4 flex items-start justify-between gap-3">
        <div>
          <h2 class="text-lg font-bold">${money(item.amount)}</h2>
          <p class="mt-1 text-sm text-brand-muted">${item.stock} vouchers in stock</p>
        </div>
        <span class="rounded-full ${item.status === "Limited" ? "voucher-status-limited bg-amber-100 text-amber-800" : "voucher-status-available bg-emerald-100 text-emerald-900"} px-2.5 py-1 text-xs font-semibold">${item.status}</span>
      </div>
      ${authState.loggedIn
        ? `<button class="mt-4 w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F] disabled:cursor-not-allowed disabled:bg-slate-300" data-add="${item.id}" type="button" ${item.stock <= 0 ? "disabled" : ""}>Add to Cart</button>`
        : `<a class="mt-4 flex w-full justify-center rounded-ui bg-brand-yellow px-4 py-2.5 text-sm font-bold text-brand-ink hover:bg-[#E8BB1E]" href="signin">Sign in to Purchase</a>`}
    </article>`).join("");
  grid.querySelectorAll("[data-add]").forEach((button) => button.addEventListener("click", () => addToCart(button.dataset.add)));
  fillTotals();
  renderRecommendations();
}

function fillTotals() {
  const currentSubtotal = subtotal();
  const discountPercent = discountConfig.rate * 100;
  const values = {
    subtotal: money(currentSubtotal),
    discount: `-${money(discount())}`,
    partnerFee: money(partnerFeeAmount()),
    total: money(total()),
    items: count(),
    discountRate: `(${discountPercent.toLocaleString("en-GH", { maximumFractionDigits: 2 })}%)`
  };
  Object.entries(values).forEach(([key, value]) => {
    document.querySelectorAll(`[data-${key}]`).forEach((el) => el.textContent = value);
  });
  document.querySelectorAll("[data-discount-note]").forEach((el) => {
    if (currentSubtotal < discountConfig.minAmount) {
      el.textContent = `Discount starts from ${money(discountConfig.minAmount)}. Current configured rate is ${discountPercent.toLocaleString("en-GH", { maximumFractionDigits: 2 })}%.`;
    } else {
      el.textContent = `Discount base is capped at ${money(discountConfig.maxCap)}. Current configured rate is ${discountPercent.toLocaleString("en-GH", { maximumFractionDigits: 2 })}%.`;
    }
  });
  document.querySelectorAll("[data-partner-fee-row]").forEach((row) => {
    const hasPartnerFee = partnerFeePercent() > 0;
    row.classList.toggle("hidden", !hasPartnerFee);
    row.classList.toggle("flex", hasPartnerFee);
  });
  document.querySelectorAll("[data-partner-fee-rate]").forEach((el) => {
    el.textContent = `(${partnerFeePercent().toLocaleString("en-GH", { maximumFractionDigits: 2 })}%)`;
  });
}

function renderCart() {
  const list = document.getElementById("cart-list");
  if (!list) return;
  const items = cart();
  fillTotals();
  if (!items.length) {
    list.innerHTML = `<div class="p-6 text-center"><p class="font-semibold">Your cart is empty</p><p class="mt-1 text-sm text-brand-muted">Choose vouchers from the store to begin.</p><a class="mt-4 inline-flex rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white" href="store">Go to Store</a></div>`;
    renderRecommendations();
    return;
  }
  list.innerHTML = items.map((item) => `
    <article class="grid gap-4 p-4 sm:grid-cols-[minmax(0,1fr)_170px_96px] sm:items-center">
      <div class="flex items-center gap-3">
        <img class="h-14 w-24 shrink-0 rounded-ui border border-brand-line object-cover" src="${item.image}" alt="Star Oil ${money(item.amount)} fuel voucher preview" />
        <div><h2 class="text-sm font-bold">Fuel Voucher ${money(item.amount)}</h2><p class="mt-1 text-sm text-brand-muted">${item.status} · ${item.stock} in stock</p></div>
      </div>
      <div class="flex w-fit items-center rounded-ui border border-brand-line">
        <button class="px-3 py-2 text-sm font-bold" data-qty="${item.id}" data-delta="-1" type="button">−</button>
        <span class="min-w-10 px-3 text-center text-sm font-bold">${item.quantity}</span>
        <button class="px-3 py-2 text-sm font-bold" data-qty="${item.id}" data-delta="1" type="button">+</button>
      </div>
      <div class="flex items-center justify-between gap-3 sm:block sm:text-right">
        <p class="text-sm font-bold">${money(item.amount * item.quantity)}</p>
        <button class="text-sm font-semibold text-red-700" data-remove="${item.id}" type="button">Remove</button>
      </div>
    </article>`).join("");
  list.querySelectorAll("[data-qty]").forEach((button) => button.addEventListener("click", () => changeQty(button.dataset.qty, Number(button.dataset.delta))));
  list.querySelectorAll("[data-remove]").forEach((button) => button.addEventListener("click", () => removeItem(button.dataset.remove)));
  renderRecommendations();
}

function renderCheckout() {
  const list = document.getElementById("checkout-items");
  if (!list) return;
  const method = localStorage.getItem("staroil:paymentLabel") || localStorage.getItem("staroil:payment") || "Tingg (MoMo)";
  document.querySelectorAll("[data-payment-method]").forEach((el) => el.textContent = method);
  list.innerHTML = cart().length ? cart().map((item) => `<div class="flex justify-between gap-3 text-sm"><span class="text-brand-muted">${item.quantity} × ${money(item.amount)} voucher</span><span class="font-bold">${money(item.quantity * item.amount)}</span></div>`).join("") : `<p class="text-sm text-brand-muted">No vouchers selected.</p>`;
  syncCheckoutForm();
  fillTotals();
}

function renderWalletBalanceDisplay() {
  const balanceNodes = document.querySelectorAll("[data-wallet-balance], [data-wallet-balance-inline]");
  const visibleValue = walletBalance === null ? "GHS 0.00" : money(walletBalance);
  balanceNodes.forEach((node) => {
    node.textContent = walletBalanceVisible ? visibleValue : "*****";
  });
  document.querySelectorAll("[data-wallet-visibility-toggle]").forEach((button) => {
    if (button.type === "checkbox") {
      button.checked = walletBalanceVisible;
      button.setAttribute("aria-checked", String(walletBalanceVisible));
    } else {
      button.textContent = walletBalanceVisible ? "Hide Balance" : "Show Balance";
      button.setAttribute("aria-pressed", String(!walletBalanceVisible));
    }
  });
  document.querySelectorAll("[data-wallet-visibility-label]").forEach((label) => {
    label.textContent = walletBalanceVisible ? "Shown" : "Hidden";
  });
  document.querySelectorAll("[data-wallet-switch]").forEach((switchNode) => {
    switchNode.classList.toggle("bg-brand-blue", walletBalanceVisible);
    switchNode.classList.toggle("bg-slate-400", !walletBalanceVisible);
  });
}

async function renderWalletBalance() {
  const balanceNodes = document.querySelectorAll("[data-wallet-balance], [data-wallet-balance-inline]");
  const statusNodes = document.querySelectorAll("[data-wallet-status]");
  if (!balanceNodes.length && !statusNodes.length) return;

  balanceNodes.forEach((node) => {
    node.innerHTML = `<span class="inline-flex items-center gap-2"><span class="staroil-spinner !h-4 !w-4 !border-2"></span><span>Loading...</span></span>`;
  });
  statusNodes.forEach((node) => {
    node.textContent = "Checking wallet balance...";
  });

  try {
    const response = await fetch("wallet_balance", {
      headers: { Accept: "application/json" }
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok || payload.status !== "success") {
      throw new Error(payload.message || "Wallet balance could not be loaded.");
    }

    const data = payload.data || {};
    walletBalance = Number(data.balance || 0);
    renderWalletBalanceDisplay();
    statusNodes.forEach((node) => {
      node.textContent = `Status: ${data.wallet_status || "active"} | ${data.currency || "GHS"}`;
    });
  } catch (error) {
    walletBalance = null;
    renderWalletBalanceDisplay();
    statusNodes.forEach((node) => {
      node.textContent = error.message || "Wallet balance unavailable.";
    });
    if (document.querySelector("[data-wallet-balance]")) {
      toast("error", "Wallet unavailable", error.message || "Wallet balance could not be loaded.");
    }
  }
}

function syncPaymentControls() {
  const form = document.getElementById("checkout-form");
  const defaultGateway = form?.querySelector("[data-payment-gateway-field]")?.value || "Hubtel";
  const gateway = localStorage.getItem("staroil:payment") || defaultGateway;
  const normalizedGateway = gateway === "Tingg" ? "Tingg" : gateway === "Wallet" ? "Wallet" : "Hubtel";
  const selectedButton = document.querySelector(`[data-payment="${normalizedGateway}"]`);
  const label = selectedButton?.dataset.paymentLabel || localStorage.getItem("staroil:paymentLabel") || normalizedGateway;

  localStorage.setItem("staroil:payment", normalizedGateway);
  localStorage.setItem("staroil:paymentLabel", label);

  document.querySelectorAll("[data-payment-method]").forEach((el) => {
    el.textContent = label;
  });

  document.querySelectorAll("[data-payment]").forEach((button) => {
    const selected = button.dataset.payment === normalizedGateway;
    button.classList.toggle("border-brand-blue", selected);
    button.classList.toggle("bg-[#EEF7FF]", selected);
    button.classList.toggle("text-brand-blue", selected);
    button.classList.toggle("border-brand-line", !selected);
    button.classList.toggle("bg-white", !selected);
    button.classList.toggle("text-brand-ink", !selected);
    button.setAttribute("aria-pressed", String(selected));
  });

  syncCheckoutForm();
}

function syncCheckoutForm() {
  const form = document.getElementById("checkout-form"); 
  if (!form) return;

  const defaultGateway = form.querySelector("[data-payment-gateway-field]")?.value || "Hubtel";
  const gateway = localStorage.getItem("staroil:payment") || defaultGateway;
  const normalizedGateway = gateway === "Tingg" ? "Tingg" : gateway === "Wallet" ? "Wallet" : "Hubtel";
  const action = normalizedGateway === "Hubtel" ? form.dataset.hubtelAction : normalizedGateway === "Wallet" ? form.dataset.walletAction : form.dataset.tinggAction;

  form.action = action || "checkout_process";
  form.querySelector("[data-totalamount-field]")?.setAttribute("value", String(subtotal()));
  form.querySelector("[data-discounted-total-field]")?.setAttribute("value", String(total()));
  form.querySelector("[data-payment-gateway-field]")?.setAttribute("value", normalizedGateway);
  form.querySelector("[data-cart-payload-field]")?.setAttribute("value", JSON.stringify(cart()));
}

function statusBadge(status) {
  const classes = {
    Pending: "badge-pending bg-amber-100 text-amber-800",
    Activated: "badge-activated bg-emerald-100 text-emerald-800",
    Redeemed: "badge-redeemed bg-slate-100 text-slate-700",
    "Not Redeemed": "badge-not-redeemed bg-slate-900 text-white",
    Expired: "badge-expired bg-red-100 text-red-800"
  };
  return `<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${classes[status] || classes.Pending}">${escapeHtml(status)}</span>`;
}

function formatReadableDate(value, includeTime = true) {
  const raw = String(value ?? "").trim();
  if (!raw || raw === "N/A") return "N/A";

  const match = raw.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?/);
  if (!match) return raw;

  const [, year, month, day, hour = "00", minute = "00", second = "00"] = match;
  const date = new Date(Number(year), Number(month) - 1, Number(day), Number(hour), Number(minute), Number(second));
  if (Number.isNaN(date.getTime())) return raw;

  return new Intl.DateTimeFormat("en-GH", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    ...(includeTime && match[4] ? { hour: "2-digit", minute: "2-digit" } : {})
  }).format(date);
}

function normalizePurchasedVoucher(row) {
  const expired = row.expiry_date && row.expiry_date <= new Date().toISOString().slice(0, 10);
  const redeemed = Number(row.redeemed_status) === 1;
  const active = Number(row.status) === 1;
  const status = redeemed ? "Redeemed" : expired ? "Expired" : active ? "Activated" : "Pending";
  const rawOrderDate = row.order_date || "";

  return {
    id: row.id,
    orderCode: row.order_code || "N/A",
    voucherCode: row.voucher_code || "N/A",
    voucherAuth: row.voucher_auth || "N/A",
    amount: Number(row.amount || 0),
    rawOrderDate,
    orderDate: formatReadableDate(rawOrderDate),
    expiryDate: row.expiry_date || "N/A",
    stationName: row.station_name || "N/A",
    redeemedPhone: row.redeemed_phone || "N/A",
    redeemedStatus: redeemed ? "Redeemed" : "Not Redeemed",
    status,
    canModify: !redeemed
  };
}

function purchasedVoucherKey(voucher) {
  return [
    voucher.id || "",
    voucher.voucherCode || "",
    voucher.voucherAuth || "",
    voucher.orderCode || "",
    voucher.amount || ""
  ].join("|");
}

function uniquePurchasedVouchers(items) {
  const seen = new Set();
  return items.filter((voucher) => {
    const key = purchasedVoucherKey(voucher);
    if (seen.has(key)) return false;
    seen.add(key);
    return true;
  });
}

async function loadPurchasedVouchers() {
  if (vouchersLoadedFromEndpoint) return;
  const table = document.getElementById("voucher-table");
  const grid = document.getElementById("voucher-grid-view");

  if (table) {
    table.innerHTML = `<tr><td class="px-4 py-8" colspan="11">${loadingSpinner("Loading purchased vouchers...")}</td></tr>`;
  }
  if (grid) {
    grid.innerHTML = `<div class="rounded-ui border border-brand-line bg-white p-6 shadow-soft sm:col-span-2 xl:col-span-3">${loadingSpinner("Loading purchased vouchers...")}</div>`;
  }

  try {
    const response = await fetch("fetch_bene_vouchers", {
      headers: { Accept: "application/json" }
    });

    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(payload.message || `Purchased voucher request failed with ${response.status}`);
    }

    if (payload.status !== "success" || !Array.isArray(payload.data)) {
      vouchers = [];
      toast("warning", "No purchased vouchers", payload.message || "No purchased vouchers were returned for this account.");
    } else {
      vouchers = uniquePurchasedVouchers(payload.data.map(normalizePurchasedVoucher));
    }
  } catch (error) {
    vouchers = [];
    toast("error", "Voucher history unavailable", error.message || "Purchased vouchers could not be loaded right now.");
  } finally {
    vouchersLoadedFromEndpoint = true;
  }
}

function filteredVouchers() {
  const q = (document.getElementById("voucher-search")?.value || "").toLowerCase();
  const status = document.getElementById("status-filter")?.value || "all";

  return vouchers.filter((v) => {
    const haystack = [
      v.orderCode,
      v.voucherCode,
      v.voucherAuth,
      v.amount,
      v.orderDate,
      v.expiryDate,
      v.stationName,
      v.redeemedPhone,
      v.redeemedStatus,
      v.status
    ].join(" ").toLowerCase();

    const matchesSearch = haystack.includes(q);
    const matchesStatus = status === "all" || v.status === status || v.redeemedStatus === status;
    return matchesSearch && matchesStatus;
  });
}

function voucherActions(voucher, mode = "table") {
  if (!voucher.canModify) return `<span class="text-xs font-semibold text-brand-muted">Locked</span>`;
  const sizeClass = mode === "grid" ? "flex-1 justify-center" : "";
  return `
    <a class="inline-flex ${sizeClass} rounded-ui border border-brand-blue px-3 py-1.5 text-xs font-semibold text-brand-blue hover:bg-[#EEF7FF]" href="voucher_update?title=${encodeURIComponent(voucher.id)}" target="_blank" rel="noopener">Edit</a>
    <a class="inline-flex ${sizeClass} rounded-ui border border-brand-line px-3 py-1.5 text-xs font-semibold text-brand-ink hover:bg-brand-soft" href="vouchers_print_single?title=${encodeURIComponent(voucher.id)}" target="_blank" rel="noopener">Print</a>
  `;
}

function csvCell(value) {
  return `"${String(value ?? "").replace(/"/g, '""')}"`;
}

async function exportVouchers() {
  await loadPurchasedVouchers();
  const rows = filteredVouchers();

  if (!rows.length) {
    toast("warning", "Nothing to export", "No vouchers match the current filters.");
    return;
  }

  const headers = [
    "Order Code",
    "Voucher Code",
    "Voucher Auth",
    "Amount",
    "Order Date",
    "Expiry Date",
    "Station",
    "Redeemed Status",
    "Redeemed Phone",
    "Status"
  ];

  const csvRows = rows.map((voucher) => [
    voucher.orderCode,
    voucher.voucherCode,
    voucher.voucherAuth,
    voucher.amount,
    voucher.orderDate,
    voucher.expiryDate,
    voucher.stationName,
    voucher.redeemedStatus,
    voucher.redeemedPhone,
    voucher.status
  ].map(csvCell).join(","));

  const csv = [headers.map(csvCell).join(","), ...csvRows].join("\r\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = `staroil-vouchers-${new Date().toISOString().slice(0, 10)}.csv`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
  toast("success", "Export ready", `${rows.length} voucher${rows.length === 1 ? "" : "s"} exported to CSV.`);
}

function setVoucherView(nextView) {
  voucherView = nextView === "grid" ? "grid" : "table";
  localStorage.setItem("staroil:voucherView", voucherView);

  document.getElementById("voucher-table-view")?.classList.toggle("hidden", voucherView !== "table");
  document.getElementById("voucher-grid-view")?.classList.toggle("hidden", voucherView !== "grid");
  document.getElementById("voucher-grid-view")?.classList.toggle("grid", voucherView === "grid");

  document.querySelectorAll("[data-voucher-view]").forEach((button) => {
    const active = button.dataset.voucherView === voucherView;
    button.classList.toggle("bg-brand-blue", active);
    button.classList.toggle("text-white", active);
    button.classList.toggle("text-brand-muted", !active);
  });
}

async function renderVouchers() {
  const table = document.getElementById("voucher-table");
  const grid = document.getElementById("voucher-grid-view");
  if (!table && !grid) return;

  setVoucherView(voucherView);
  await loadPurchasedVouchers();

  const rows = filteredVouchers();
  table.innerHTML = rows.map((v) => `
    <tr>
      <td class="whitespace-nowrap px-4 py-4"><div class="flex gap-2">${voucherActions(v)}</div></td>
      <td class="whitespace-nowrap px-4 py-4 text-sm font-bold">${escapeHtml(v.orderCode)}</td>
      <td class="whitespace-nowrap px-4 py-4 text-sm"><span class="rounded-full bg-brand-blue px-2.5 py-1 text-xs font-semibold text-white">${escapeHtml(v.voucherCode)}</span></td>
      <td class="whitespace-nowrap px-4 py-4 text-sm">${escapeHtml(v.voucherAuth)}</td>
      <td class="whitespace-nowrap px-4 py-4 text-sm font-bold">${money(v.amount)}</td>
      <td class="whitespace-nowrap px-4 py-4 text-sm text-brand-muted">${escapeHtml(v.orderDate)}</td>
      <td class="whitespace-nowrap px-4 py-4 text-sm text-brand-muted">${escapeHtml(v.expiryDate)}</td>
      <td class="whitespace-nowrap px-4 py-4 text-sm">${escapeHtml(v.stationName)}</td>
      <td class="whitespace-nowrap px-4 py-4">${statusBadge(v.redeemedStatus)}</td>
      <td class="whitespace-nowrap px-4 py-4 text-sm">${escapeHtml(v.redeemedPhone)}</td>
      <td class="whitespace-nowrap px-4 py-4">${statusBadge(v.status)}</td>
    </tr>`).join("") || `<tr><td class="px-4 py-8 text-center text-sm text-brand-muted" colspan="11">No vouchers match the current filters.</td></tr>`;

  if (grid) {
    grid.innerHTML = rows.map((v) => `
      <article class="rounded-ui border border-brand-line bg-white p-4 shadow-soft">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-xs font-semibold uppercase text-brand-muted">Order ${escapeHtml(v.orderCode)}</p>
            <h2 class="mt-1 text-lg font-bold">${money(v.amount)}</h2>
          </div>
          ${statusBadge(v.status)}
        </div>
        <dl class="mt-4 grid gap-3 text-sm">
          <div class="rounded-ui bg-brand-soft p-3">
            <dt class="text-xs font-semibold uppercase text-brand-muted">Voucher Code</dt>
            <dd class="mt-1 break-all font-bold">${escapeHtml(v.voucherCode)}</dd>
          </div>
          <div class="rounded-ui bg-brand-soft p-3">
            <dt class="text-xs font-semibold uppercase text-brand-muted">Auth Code</dt>
            <dd class="mt-1 break-all font-semibold">${escapeHtml(v.voucherAuth)}</dd>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-ui bg-brand-soft p-3">
              <dt class="text-xs font-semibold uppercase text-brand-muted">Order Date</dt>
              <dd class="mt-1 font-semibold">${escapeHtml(v.orderDate)}</dd>
            </div>
            <div class="rounded-ui bg-brand-soft p-3">
              <dt class="text-xs font-semibold uppercase text-brand-muted">Expiry</dt>
              <dd class="mt-1 font-semibold">${escapeHtml(v.expiryDate)}</dd>
            </div>
          </div>
          <div class="rounded-ui bg-brand-soft p-3">
            <dt class="text-xs font-semibold uppercase text-brand-muted">Station</dt>
            <dd class="mt-1 font-semibold">${escapeHtml(v.stationName)}</dd>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-ui bg-brand-soft p-3">
              <dt class="text-xs font-semibold uppercase text-brand-muted">Redeemed</dt>
              <dd class="mt-2">${statusBadge(v.redeemedStatus)}</dd>
            </div>
            <div class="rounded-ui bg-brand-soft p-3">
              <dt class="text-xs font-semibold uppercase text-brand-muted">Status</dt>
              <dd class="mt-2">${statusBadge(v.status)}</dd>
            </div>
          </div>
          <div class="rounded-ui bg-brand-soft p-3">
            <dt class="text-xs font-semibold uppercase text-brand-muted">Redeemed Phone</dt>
            <dd class="mt-1 font-semibold">${escapeHtml(v.redeemedPhone)}</dd>
          </div>
        </dl>
        <div class="mt-4 flex gap-2">${voucherActions(v, "grid")}</div>
      </article>`).join("") || `<div class="rounded-ui border border-brand-line bg-white p-6 text-center text-sm text-brand-muted shadow-soft sm:col-span-2 xl:col-span-3">No vouchers match the current filters.</div>`;
  }
}

async function fetchAnalyticsJson(endpoint) {
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 12000);

  try {
    const response = await fetch(endpoint, {
      headers: { Accept: "application/json" },
      signal: controller.signal
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok || payload.status !== "success") {
      throw new Error(payload.message || `${endpoint} could not be loaded.`);
    }
    return payload;
  } catch (error) {
    if (error.name === "AbortError") {
      throw new Error(`${endpoint} took too long to respond.`);
    }
    throw error;
  } finally {
    clearTimeout(timeout);
  }
}

function analyticsTile(title, value, note, tone = "blue") {
  const tones = {
    blue: "bg-[#EEF7FF] text-brand-blue",
    yellow: "bg-brand-yellow text-brand-ink",
    green: "bg-emerald-50 text-emerald-800",
    red: "bg-red-50 text-red-800",
    slate: "bg-brand-soft text-brand-ink"
  };
  return `
    <article class="rounded-ui border border-brand-line bg-white p-4 shadow-soft">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-sm font-semibold text-brand-muted">${escapeHtml(title)}</p>
          <p class="mt-2 text-3xl font-bold">${escapeHtml(value)}</p>
          <p class="mt-2 text-sm text-brand-muted">${escapeHtml(note)}</p>
        </div>
        <span class="rounded-ui px-3 py-2 text-xs font-bold ${tones[tone] || tones.blue}">Live</span>
      </div>
    </article>`;
}

function emptyAnalytics(message) {
  return `<div class="rounded-ui border border-brand-line bg-brand-soft p-4 text-sm font-semibold text-brand-muted">${escapeHtml(message)}</div>`;
}

function horizontalBars(items, options = {}) {
  const max = Math.max(...items.map((item) => Number(item.value || 0)), 0);
  if (!items.length || max <= 0) return emptyAnalytics(options.empty || "No data available.");

  return `
    <div class="grid gap-3">
      ${items.map((item) => {
        const percent = Math.max(4, Math.round((Number(item.value || 0) / max) * 100));
        return `
          <div>
            <div class="mb-1 flex justify-between gap-3 text-xs font-semibold">
              <span class="text-brand-muted">${escapeHtml(item.label)}</span>
              <span>${escapeHtml(item.display || String(item.value))}</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-brand-soft">
              <div class="h-full rounded-full ${options.barClass || "bg-brand-blue"}" style="width:${percent}%"></div>
            </div>
          </div>`;
      }).join("")}
    </div>`;
}

function donutChart(items) {
  const totalCount = items.reduce((sum, item) => sum + Number(item.value || 0), 0);
  if (totalCount <= 0) return emptyAnalytics("No voucher status data available.");

  const colors = ["#2178BD", "#10B981", "#64748B", "#EF4444", "#FDCD21"];
  let cursor = 0;
  const stops = items.map((item, index) => {
    const start = cursor;
    const end = cursor + (Number(item.value || 0) / totalCount) * 100;
    cursor = end;
    return `${colors[index % colors.length]} ${start}% ${end}%`;
  }).join(", ");

  return `
    <div class="grid gap-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center">
      <div class="mx-auto grid h-44 w-44 place-items-center rounded-full" style="background: conic-gradient(${stops});">
        <div class="grid h-24 w-24 place-items-center rounded-full bg-white text-center">
          <span><strong class="block text-2xl">${totalCount}</strong><small class="text-xs font-semibold text-brand-muted">vouchers</small></span>
        </div>
      </div>
      <div class="grid gap-2">
        ${items.map((item, index) => `
          <div class="flex items-center justify-between gap-3 rounded-ui border border-brand-line p-3 text-sm">
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background:${colors[index % colors.length]}"></span>${escapeHtml(item.label)}</span>
            <strong>${Number(item.value || 0).toLocaleString("en-GH")}</strong>
          </div>`).join("")}
      </div>
    </div>`;
}

function dateDiffDays(dateString) {
  const date = new Date(`${dateString}T00:00:00Z`);
  if (Number.isNaN(date.getTime())) return null;
  const today = new Date();
  const utcToday = Date.UTC(today.getUTCFullYear(), today.getUTCMonth(), today.getUTCDate());
  return Math.ceil((date.getTime() - utcToday) / 86400000);
}

function analyticsPayloadTotal(payload, fallback) {
  const candidates = [
    payload?.total,
    payload?.total_count,
    payload?.total_records,
    payload?.recordsTotal,
    payload?.count,
    payload?.purchased_count,
    payload?.total_vouchers,
    payload?.meta?.total,
    payload?.meta?.total_count,
    payload?.pagination?.total
  ];

  for (const value of candidates) {
    const parsed = Number(value);
    if (Number.isFinite(parsed) && parsed >= 0) return parsed;
  }

  return fallback;
}

async function renderAnalyticsDashboard() {
  const tiles = document.querySelector("[data-analytics-tiles]");
  const statusChart = document.querySelector("[data-chart-status]");
  const monthlyChart = document.querySelector("[data-chart-monthly]");
  const denominationChart = document.querySelector("[data-chart-denominations]");
  const cartChart = document.querySelector("[data-chart-cart]");
  const signals = document.querySelector("[data-analytics-signals]");
  if (!tiles && !statusChart && !monthlyChart && !denominationChart && !cartChart && !signals) return;

  const loading = loadingSpinner("Loading analytics...");
  if (tiles) tiles.innerHTML = `<div class="rounded-ui border border-brand-line bg-white p-4 shadow-soft sm:col-span-2 xl:col-span-4">${loading}</div>`;
  [statusChart, monthlyChart, denominationChart, cartChart, signals].forEach((node) => {
    if (node) node.innerHTML = loading;
  });

  try {
    const purchasedPayload = await fetchAnalyticsJson("fetch_bene_vouchers");
    const walletPayload = await fetchAnalyticsJson("wallet_balance").catch(() => ({ data: { balance: 0 } }));

    const purchased = Array.isArray(purchasedPayload.data) ? uniquePurchasedVouchers(purchasedPayload.data.map(normalizePurchasedVoucher)) : [];
    const purchasedTotalCount = analyticsPayloadTotal(purchasedPayload, purchased.length);
    const walletValue = Number(walletPayload.data?.balance || 0);
    const cartItems = cart();
    const cartValue = subtotal();

    const totals = purchased.reduce((acc, voucher) => {
      acc.count += 1;
      acc.value += Number(voucher.amount || 0);
      acc.statusCounts[voucher.status] = (acc.statusCounts[voucher.status] || 0) + 1;
      acc.redemptionCounts[voucher.redeemedStatus] = (acc.redemptionCounts[voucher.redeemedStatus] || 0) + 1;
      const days = dateDiffDays(voucher.expiryDate);
      if (days !== null && days >= 0 && days <= 14 && voucher.redeemedStatus !== "Redeemed") acc.expiringSoon += 1;
      return acc;
    }, { count: 0, value: 0, statusCounts: {}, redemptionCounts: {}, expiringSoon: 0 });

    const pendingCount = totals.statusCounts.Pending || 0;
    const activatedCount = totals.statusCounts.Activated || 0;
    const redeemedCount = totals.statusCounts.Redeemed || 0;
    const expiredCount = totals.statusCounts.Expired || 0;
    const notRedeemedCount = totals.redemptionCounts["Not Redeemed"] || 0;
    const activeValue = purchased.filter((voucher) => voucher.status === "Activated").reduce((sum, voucher) => sum + Number(voucher.amount || 0), 0);
    const redemptionRate = purchasedTotalCount ? Math.round((redeemedCount / purchasedTotalCount) * 100) : 0;
    const activationReadyRate = purchasedTotalCount ? Math.round((activatedCount / purchasedTotalCount) * 100) : 0;

    if (tiles) {
      tiles.innerHTML = [
        analyticsTile("Purchased Vouchers", purchasedTotalCount.toLocaleString("en-GH"), `${money(totals.value)} visible value`, "blue"),
        analyticsTile("Wallet Balance", money(walletValue), "Available voucher credit", "yellow"),
        analyticsTile("Activated Value", money(activeValue), `${activatedCount} active voucher(s)`, "green"),
        analyticsTile("Current Cart", money(cartValue), `${count()} voucher item(s) selected`, "slate")
      ].join("");
    }

    if (statusChart) {
      statusChart.innerHTML = donutChart([
        { label: "Pending Activation", value: pendingCount },
        { label: "Activated", value: activatedCount },
        { label: "Redeemed", value: redeemedCount },
        { label: "Expired", value: expiredCount }
      ]);
    }

    const monthly = new Map();
    purchased.forEach((voucher) => {
      const sourceDate = voucher.rawOrderDate || voucher.orderDate;
      const key = /^\d{4}-\d{2}/.test(sourceDate) ? sourceDate.slice(0, 7) : "Unknown";
      monthly.set(key, (monthly.get(key) || 0) + Number(voucher.amount || 0));
    });
    if (monthlyChart) {
      monthlyChart.innerHTML = horizontalBars(
        Array.from(monthly.entries()).sort(([a], [b]) => a.localeCompare(b)).slice(-8).map(([label, value]) => ({ label, value, display: money(value) })),
        { barClass: "bg-brand-blue", empty: "No monthly purchase data available." }
      );
    }

    const denominations = new Map();
    purchased.forEach((voucher) => {
      const key = money(Number(voucher.amount || 0));
      denominations.set(key, (denominations.get(key) || 0) + 1);
    });
    if (denominationChart) {
      denominationChart.innerHTML = horizontalBars(
        Array.from(denominations.entries()).sort((a, b) => b[1] - a[1]).map(([label, value]) => ({ label, value, display: `${value} voucher${value === 1 ? "" : "s"}` })),
        { barClass: "bg-brand-yellow", empty: "No denomination data available." }
      );
    }

    if (cartChart) {
      cartChart.innerHTML = horizontalBars(
        cartItems.map((item) => ({
          label: money(Number(item.amount || 0)),
          value: Number(item.amount || 0) * Number(item.quantity || 0),
          display: `${Number(item.quantity || 0)} selected | ${money(Number(item.amount || 0) * Number(item.quantity || 0))}`
        })),
        { barClass: "bg-emerald-600", empty: "No vouchers currently selected in your cart." }
      );
    }

    if (signals) {
      signals.innerHTML = [
        analyticsTile("Redemption Rate", `${redemptionRate}%`, `${redeemedCount} redeemed of ${purchasedTotalCount}`, "green"),
        analyticsTile("Ready for Redemption", `${activationReadyRate}%`, `${activatedCount} activated voucher(s) ready for redemption`, "green"),
        analyticsTile("Pending Activation", String(pendingCount), "Purchased vouchers not yet activated for redemption", "yellow"),
        analyticsTile("Expiring Soon", String(totals.expiringSoon), "Unredeemed vouchers expiring in 14 days", totals.expiringSoon ? "red" : "green"),
        analyticsTile("Not Redeemed", String(notRedeemedCount), "Purchased vouchers not yet redeemed", "blue")
      ].join("");
    }
  } catch (error) {
    const message = error.message || "Analytics could not be loaded.";
    if (tiles) tiles.innerHTML = `<div class="rounded-ui border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 sm:col-span-2 xl:col-span-4">${escapeHtml(message)}</div>`;
    [statusChart, monthlyChart, denominationChart, cartChart, signals].forEach((node) => {
      if (node) node.innerHTML = emptyAnalytics(message);
    });
    toast("error", "Analytics unavailable", message);
  }
}

function bindAuth() {
  document.querySelectorAll("[data-auth-form]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      const next = form.dataset.next;
      if (form.dataset.code === "true") {
        const input = form.querySelector("input[inputmode='numeric']");
        if (!/^\d{6}$/.test(input.value.trim())) {
          toast("error", "Invalid code", "Enter a 6-digit numeric verification code.");
          return;
        }
      }
      if (next) location.href = next;
    });
  });
}

function bindMenuToggle() {
  document.querySelectorAll("[data-menu-toggle]").forEach((button) => {
    if (button.dataset.menuBound === "true") return;
    button.dataset.menuBound = "true";

    button.addEventListener("click", () => {
      const menuId = button.getAttribute("aria-controls");
      const menu = menuId ? document.getElementById(menuId) : document.querySelector("[data-menu]");
      if (!menu) return;

      const willOpen = menu.classList.contains("hidden");
      menu.classList.toggle("hidden", !willOpen);
      menu.classList.toggle("flex", willOpen);
      button.setAttribute("aria-expanded", String(willOpen));
    });
  });
}

function bindPasswordToggles() {
  document.querySelectorAll("[data-toggle-password]").forEach((button) => {
    if (button.dataset.passwordToggleBound === "true") return;
    button.dataset.passwordToggleBound = "true";

    button.addEventListener("click", () => {
      const fieldId = button.getAttribute("aria-controls");
      const field = fieldId ? document.getElementById(fieldId) : button.parentElement?.querySelector("[data-password-field]");
      if (!field) return;

      const shouldShow = field.type === "password";
      field.type = shouldShow ? "text" : "password";
      button.textContent = shouldShow ? "Hide" : "Show";
      button.setAttribute("aria-pressed", String(shouldShow));
      field.focus({ preventScroll: true });
    });
  });
}

async function bindPage() {
  ensureRuntimeStyles();
  await registerPwa();
  bindThemeControls();
  bindMenuToggle();
  bindPasswordToggles();
  await loadAuthStatus();
  normalizeSavedCartImages();
  initializeUserActivityTracking();
  updateShell();
  showMfaSetupModal();
  initializeAbandonedCartTracking();
  clearCartAfterPaymentReturn();
  renderStore();
  renderCart();
  renderCheckout();
  renderWalletBalance();
  renderVouchers();
  renderAnalyticsDashboard();
  bindAuth();
  fillTotals();
  syncPaymentControls();

  document.querySelectorAll("[data-payment]").forEach((button) => button.addEventListener("click", (event) => {
    if (!cart().length) {
      event.preventDefault();
      toast("error", "Cart is empty", "Add at least one voucher before choosing a payment gateway.");
      return;
    }
    localStorage.setItem("staroil:payment", button.dataset.payment);
    localStorage.setItem("staroil:paymentLabel", button.dataset.paymentLabel || button.dataset.payment);
    syncPaymentControls();
    toast("info", "Payment selected", `${button.dataset.paymentLabel || button.dataset.payment} will be used for payment.`);
  }));
  document.getElementById("checkout-link")?.addEventListener("click", (event) => {
    if (!cart().length) {
      event.preventDefault();
      toast("error", "Cart is empty", "Add at least one voucher before checkout.");
    }
  });
  document.getElementById("checkout-form")?.addEventListener("submit", (event) => {
    if (!cart().length) {
      event.preventDefault();
      toast("error", "No order to place", "Your cart is empty.");
      return;
    }
    const selectedPayment = localStorage.getItem("staroil:payment") || document.querySelector("[data-payment-gateway-field]")?.value || "Hubtel";
    if (selectedPayment === "Wallet") {
      const amountDue = total();
      if (walletBalance === null || walletBalance < amountDue) {
        event.preventDefault();
        const available = walletBalance === null ? "unavailable" : money(walletBalance);
        const shortfall = walletBalance === null ? "" : ` You need ${money(amountDue - walletBalance)} more.`;
        toast("error", "Insufficient wallet balance", `Wallet balance is ${available}. Voucher total is ${money(amountDue)}.${shortfall}`);
        return;
      }
    }
    trackCartEvent("checkout_started", cart());
    syncCheckoutForm();
  });
  document.getElementById("place-order")?.addEventListener("click", () => {
    if (document.getElementById("checkout-form")) return;
    if (!cart().length) {
      toast("error", "No order to place", "Your cart is empty.");
      return;
    }
    const created = cart().map((item, index) => ({
      code: `SO-FV-${Date.now().toString().slice(-6)}${index}`,
      amount: item.amount,
      date: "2026-06-10",
      status: "Pending"
    }));
    vouchers = [...created, ...vouchers];
    saveVouchers();
    trackCartEvent("checkout_success", cart());
    saveCart([], "cart_cleared_after_order");
    location.href = "vouchers";
  });
  document.getElementById("voucher-search")?.addEventListener("input", renderVouchers);
  document.getElementById("status-filter")?.addEventListener("change", renderVouchers);
  document.querySelectorAll("[data-voucher-view]").forEach((button) => button.addEventListener("click", () => {
    setVoucherView(button.dataset.voucherView);
    renderVouchers();
  }));
  document.getElementById("export-vouchers")?.addEventListener("click", exportVouchers);
  document.querySelector("[data-analytics-refresh]")?.addEventListener("click", renderAnalyticsDashboard);
  document.querySelectorAll("[data-wallet-visibility-toggle]").forEach((button) => {
    if (button.dataset.walletVisibilityBound === "true") return;
    button.dataset.walletVisibilityBound = "true";
    button.addEventListener("change", () => {
      walletBalanceVisible = button.type === "checkbox" ? button.checked : !walletBalanceVisible;
      localStorage.setItem("staroil:walletBalanceVisible", String(walletBalanceVisible));
      renderWalletBalanceDisplay();
    });
    button.addEventListener("click", () => {
      if (button.type === "checkbox") return;
      walletBalanceVisible = !walletBalanceVisible;
      localStorage.setItem("staroil:walletBalanceVisible", String(walletBalanceVisible));
      renderWalletBalanceDisplay();
    });
  });
  document.addEventListener("click", closeProfileDropdowns);
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeProfileDropdowns();
  });
  document.addEventListener("staroil:avatarChange", syncProfileDropdown);
  window.addEventListener("storage", (event) => {
    if (event.key === "staroil:profileAvatar") syncProfileDropdown();
  });
  document.getElementById("password-form")?.addEventListener("submit", (event) => {
    event.preventDefault();
    toast("success", "Password updated", "Password settings were updated for this session.");
    event.target.reset();
  });
  document.querySelector("[data-profile-save]")?.addEventListener("click", () => toast("success", "Profile saved", "Profile changes were saved for this session."));
}

document.addEventListener("DOMContentLoaded", bindPage);
window.addEventListener("pageshow", async () => {
  await loadAuthStatus();
  updateShell();
  showMfaSetupModal();
  syncPaymentControls();
  fillTotals();
});
