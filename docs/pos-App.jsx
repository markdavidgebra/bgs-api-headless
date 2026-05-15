import React, {
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
} from 'react';

// Set in .env: VITE_API_URL=http://bgs-api-blade.test/api  (or leave empty + use Vite proxy)
const API_BASE = (import.meta.env.VITE_API_URL || '/api').replace(/\/+$/, '');
const POS_PREFIX = '/pos';
const SANCTUM_CSRF_URL = `${API_BASE}/sanctum/csrf-cookie`;

const UI = {
  bg: 'linear-gradient(135deg,#fff5f9 0%,#ffe4ef 45%,#ffd6e8 100%)',
  surface: '#ffffff',
  surfaceMuted: '#fff8fb',
  border: '1px solid #f9c9dc',
  text: '#4a1530',
  textMuted: '#8b4a6b',
  primary: '#ec4899',
  primaryDark: '#db2777',
  accent: '#f472b6',
  shadow: '0 14px 40px rgba(219,39,119,0.12)',
  shadowSm: '0 6px 18px rgba(219,39,119,0.1)',
  radiusLg: 18,
  radiusMd: 12,
  font: 'ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial',
};

const PAYMENT_METHODS = [
  { value: 'cash', label: 'Cash' },
  { value: 'gcash', label: 'GCash' },
  { value: 'maya', label: 'Maya' },
  { value: 'card', label: 'Card' },
  { value: 'bank_transfer', label: 'Bank transfer' },
];

const CATALOG_TABS = [
  { key: 'product', label: 'Products' },
  { key: 'service', label: 'Services' },
  { key: 'package', label: 'Packages' },
  { key: 'membership', label: 'Memberships' },
];

function formatMoney(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0.00';
  return (
    '₱' +
    n.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  );
}

async function fetchSanctumCsrf() {
  await fetch(SANCTUM_CSRF_URL, {
    method: 'GET',
    credentials: 'include',
    headers: { Accept: 'application/json' },
  });
}

function getXsrfTokenFromCookie() {
  if (typeof document === 'undefined') return '';
  const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
  return m ? decodeURIComponent(m[1]) : '';
}

async function apiFetch(path, options = {}) {
  const url = String(path).startsWith('http')
    ? path
    : API_BASE + POS_PREFIX + path;
  const headers = new Headers(options.headers || {});
  if (!headers.has('Accept')) headers.set('Accept', 'application/json');
  const method = (options.method || 'GET').toUpperCase();
  if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
    if (!headers.has('Content-Type')) {
      headers.set('Content-Type', 'application/json');
    }
    const xsrf = getXsrfTokenFromCookie();
    if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
  }
  const res = await fetch(url, {
    ...options,
    credentials: 'include',
    headers,
  });
  const text = await res.text();
  let data = null;
  if (text) {
    try {
      data = JSON.parse(text);
    } catch {
      data = { raw: text };
    }
  }
  if (!res.ok) {
    const err = new Error(
      (data && data.message) || res.statusText || 'Request failed',
    );
    err.status = res.status;
    err.payload = data;
    throw err;
  }
  return data;
}

function lineDiscountFor(appliedAffiliate, item) {
  const lines = appliedAffiliate?.items || appliedAffiliate?.lines || [];
  if (!lines.length) return 0;
  const line = lines.find(
    (l) => l.type === item.type && Number(l.id) === Number(item.id),
  );
  return Number(line?.discount || 0);
}

function useDebounce(value, delay) {
  const [debounced, setDebounced] = useState(value);
  useEffect(() => {
    const t = setTimeout(() => setDebounced(value), delay);
    return () => clearTimeout(t);
  }, [value, delay]);
  return debounced;
}

function useToast() {
  const [toasts, setToasts] = useState([]);

  const pushToast = useCallback((t) => {
    const id =
      (typeof crypto !== 'undefined' &&
        crypto.randomUUID &&
        crypto.randomUUID()) ||
      String(Date.now() + Math.random());
    setToasts((prev) => [...prev, { id, ...t }]);
    const ms = typeof t.duration === 'number' ? t.duration : 3600;
    setTimeout(() => {
      setToasts((prev) => prev.filter((x) => x.id !== id));
    }, ms);
  }, []);

  const dismiss = useCallback((id) => {
    setToasts((prev) => prev.filter((x) => x.id !== id));
  }, []);

  return { toasts, pushToast, dismiss };
}

function Toast({ toast, onDismiss }) {
  const tone =
    toast.type === 'error'
      ? { bg: '#fff1f2', bd: '#fecdd3', fg: '#9f1239' }
      : toast.type === 'success'
        ? { bg: '#ecfdf5', bg2: '#d1fae5', bd: '#6ee7b7', fg: '#065f46' }
        : { bg: '#fffbeb', bd: '#fcd34d', fg: '#92400e' };
  return (
    <div
      style={{
        minWidth: 260,
        maxWidth: 420,
        padding: '12px 14px',
        borderRadius: 12,
        border: '1px solid ' + tone.bd,
        background: tone.bg,
        color: tone.fg,
        boxShadow: UI.shadowSm,
        display: 'flex',
        alignItems: 'flex-start',
        gap: 10,
        marginBottom: 8,
        fontFamily: UI.font,
        fontSize: 13,
        lineHeight: 1.4,
        transform: 'translateY(0)',
        opacity: 1,
        transition: 'opacity 180ms ease, transform 180ms ease',
      }}
      role="status"
    >
      <div style={{ flex: 1 }}>
        <div style={{ fontWeight: 700, marginBottom: 4 }}>
          {toast.title || (toast.type === 'error' ? 'Something went wrong' : 'Notice')}
        </div>
        {toast.message ? <div>{toast.message}</div> : null}
      </div>
      <button
        type="button"
        onClick={() => onDismiss(toast.id)}
        style={{
          border: 'none',
          background: 'transparent',
          color: tone.fg,
          cursor: 'pointer',
          fontSize: 16,
          lineHeight: 1,
          padding: 2,
        }}
        aria-label="Dismiss notification"
      >
        ×
      </button>
    </div>
  );
}

function Button({
  children,
  onClick,
  disabled,
  variant = 'primary',
  style,
  type = 'button',
}) {
  const palette =
    variant === 'ghost'
      ? { bg: 'transparent', fg: UI.primaryDark, b: '1px solid #fbcfe8' }
      : variant === 'danger'
        ? { bg: '#fef2f2', fg: '#b91c1c', b: '1px solid #fecaca' }
        : { bg: UI.primary, fg: '#fff', b: '1px solid ' + UI.primaryDark };
  return (
    <button
      type={type}
      disabled={disabled}
      onClick={onClick}
      style={{
        fontFamily: UI.font,
        fontWeight: 600,
        fontSize: 13,
        borderRadius: 10,
        padding: '8px 14px',
        cursor: disabled ? 'not-allowed' : 'pointer',
        opacity: disabled ? 0.55 : 1,
        background: palette.bg,
        color: palette.fg,
        border: palette.b,
        ...style,
      }}
    >
      {children}
    </button>
  );
}

function Field({ label, children, hint }) {
  return (
    <label style={{ display: 'block', marginBottom: 10, fontFamily: UI.font }}>
      <div
        style={{
          fontSize: 12,
          fontWeight: 700,
          color: UI.textMuted,
          marginBottom: 6,
        }}
      >
        {label}
      </div>
      {children}
      {hint ? (
        <div style={{ fontSize: 11, color: UI.textMuted, marginTop: 6 }}>{hint}</div>
      ) : null}
    </label>
  );
}

function TextInput(props) {
  const { style, ...rest } = props;
  return (
    <input
      {...rest}
      style={{
        width: '100%',
        boxSizing: 'border-box',
        borderRadius: 10,
        border: UI.border,
        padding: '10px 12px',
        fontSize: 14,
        outline: 'none',
        fontFamily: UI.font,
        background: UI.surface,
        color: UI.text,
        ...style,
      }}
    />
  );
}

function SelectInput(props) {
  const { style, children, ...rest } = props;
  return (
    <select
      {...rest}
      style={{
        width: '100%',
        boxSizing: 'border-box',
        borderRadius: 10,
        border: UI.border,
        padding: '10px 12px',
        fontSize: 14,
        outline: 'none',
        fontFamily: UI.font,
        background: UI.surface,
        color: UI.text,
        ...style,
      }}
    >
      {children}
    </select>
  );
}

function CatalogCard({ item, tab, onAdd }) {
  const price =
    typeof item.price === 'number'
      ? item.price
      : Number(item.unit_price || item.price || 0);
  const meta = [];
  if (tab === 'product') {
    if (item.sku) meta.push('SKU ' + item.sku);
    if (item.stock_quantity !== undefined) meta.push('Stock ' + item.stock_quantity);
    if (item.unit) meta.push(String(item.unit));
  }
  if (tab === 'service' && item.duration_minutes) {
    meta.push(item.duration_minutes + ' min');
  }
  if (tab === 'package' && item.validity_label) {
    meta.push(String(item.validity_label));
  }
  if (tab === 'membership' && item.duration_label) {
    meta.push(String(item.duration_label));
  }
  const stockTone =
    item.stock_status === 'out_of_stock'
      ? { fg: '#b91c1c', bg: '#fef2f2' }
      : item.stock_status === 'low_stock'
        ? { fg: '#c2410c', bg: '#fff7ed' }
        : { fg: '#047857', bg: '#ecfdf5' };

  return (
    <div
      style={{
        borderRadius: UI.radiusMd,
        border: UI.border,
        background: UI.surface,
        padding: 12,
        boxShadow: UI.shadowSm,
        display: 'flex',
        flexDirection: 'column',
        gap: 8,
        minHeight: 132,
      }}
    >
      <div style={{ fontWeight: 800, color: UI.text, fontSize: 14, lineHeight: 1.3 }}>
        {item.name}
      </div>
      <div style={{ fontSize: 12, color: UI.textMuted, lineHeight: 1.35 }}>
        {meta.length ? meta.join(' · ') : '\u00a0'}
      </div>
      {tab === 'product' && item.stock_status ? (
        <div
          style={{
            alignSelf: 'flex-start',
            fontSize: 11,
            fontWeight: 700,
            padding: '3px 8px',
            borderRadius: 999,
            color: stockTone.fg,
            background: stockTone.bg,
            border: '1px solid rgba(0,0,0,0.04)',
          }}
        >
          {String(item.stock_status).replace(/_/g, ' ')}
        </div>
      ) : (
        <div style={{ height: 21 }} />
      )}
      <div
        style={{
          marginTop: 'auto',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          gap: 10,
        }}
      >
        <div style={{ fontWeight: 900, color: UI.primaryDark, fontSize: 16 }}>
          {formatMoney(price)}
        </div>
        <Button onClick={() => onAdd(item)} style={{ padding: '6px 10px', fontSize: 12 }}>
          Add
        </Button>
      </div>
    </div>
  );
}

function CartItem({
  row,
  appliedAffiliate,
  onInc,
  onDec,
  onRemove,
  readOnly,
}) {
  const unit = Number(row.unit_price ?? row.price ?? 0);
  const qty = Number(row.quantity ?? 1);
  const lineSubtotal = unit * qty;
  const disc = appliedAffiliate ? lineDiscountFor(appliedAffiliate, row) : 0;
  const lineTotal = Math.max(0, lineSubtotal - disc);

  return (
    <div
      style={{
        border: UI.border,
        borderRadius: UI.radiusMd,
        background: UI.surfaceMuted,
        padding: 10,
        display: 'grid',
        gridTemplateColumns: '1fr auto',
        gap: 8,
      }}
    >
      <div>
        <div style={{ fontWeight: 800, color: UI.text, fontSize: 13 }}>{row.name}</div>
        <div style={{ fontSize: 11, color: UI.textMuted, marginTop: 4 }}>
          {String(row.type).toUpperCase()} #{row.id}
          {row.sku ? ' · ' + row.sku : ''}
        </div>
        <div style={{ fontSize: 12, color: UI.textMuted, marginTop: 6 }}>
          {formatMoney(unit)} × {qty}
          {disc > 0 ? (
            <span style={{ color: '#047857', fontWeight: 700 }}>
              {' '}
              − {formatMoney(disc)} discount
            </span>
          ) : null}
        </div>
      </div>
      <div style={{ textAlign: 'right' }}>
        <div style={{ fontWeight: 900, color: UI.text, fontSize: 14 }}>
          {formatMoney(lineTotal)}
        </div>
        {!readOnly ? (
          <div
            style={{
              display: 'flex',
              gap: 6,
              justifyContent: 'flex-end',
              marginTop: 8,
            }}
          >
            <Button variant="ghost" onClick={() => onDec(row._cartId)} style={{ padding: '4px 8px' }}>
              −
            </Button>
            <Button variant="ghost" onClick={() => onInc(row._cartId)} style={{ padding: '4px 8px' }}>
              +
            </Button>
            <Button
              variant="danger"
              onClick={() => onRemove(row._cartId)}
              style={{ padding: '4px 8px' }}
            >
              Remove
            </Button>
          </div>
        ) : null}
      </div>
    </div>
  );
}

function ReceiptModal({ open, onClose, receipt }) {
  if (!open || !receipt) return null;
  const payments = receipt.payments || [];
  const totals = receipt.totals || {};
  const affiliate = receipt.affiliate_code || null;

  return (
    <div
      style={{
        position: 'fixed',
        inset: 0,
        zIndex: 60,
        background: 'rgba(74,21,48,0.45)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 18,
        fontFamily: UI.font,
      }}
      role="dialog"
      aria-modal="true"
    >
      <div
        style={{
          width: 'min(720px, 100%)',
          maxHeight: '90vh',
          overflow: 'auto',
          borderRadius: UI.radiusLg,
          background: UI.surface,
          boxShadow: UI.shadow,
          border: UI.border,
          padding: 18,
        }}
      >
        <div style={{ display: 'flex', justifyContent: 'space-between', gap: 12 }}>
          <div>
            <div style={{ fontWeight: 900, fontSize: 18, color: UI.text }}>
              Receipt
            </div>
            <div style={{ color: UI.textMuted, fontSize: 12, marginTop: 6 }}>
              {receipt.message || 'POS checkout completed.'}
            </div>
          </div>
          <Button variant="ghost" onClick={onClose}>
            Close
          </Button>
        </div>

        {affiliate ? (
          <div
            style={{
              marginTop: 14,
              padding: 12,
              borderRadius: UI.radiusMd,
              background: '#fdf2f8',
              border: '1px solid #fbcfe8',
            }}
          >
            <div style={{ fontSize: 12, fontWeight: 800, color: UI.textMuted }}>
              Affiliate code applied
            </div>
            <div style={{ marginTop: 6, fontWeight: 900, color: UI.primaryDark, fontSize: 16 }}>
              {affiliate.code}
            </div>
            {affiliate.label ? (
              <div style={{ marginTop: 4, color: UI.text, fontSize: 13 }}>{affiliate.label}</div>
            ) : null}
            {affiliate.discount_label ? (
              <div style={{ marginTop: 6, color: UI.textMuted, fontSize: 12 }}>
                {affiliate.discount_label}
              </div>
            ) : null}
            <div style={{ marginTop: 8, fontSize: 12, color: UI.textMuted }}>
              Method:{' '}
              <strong style={{ color: UI.text }}>
                {String(affiliate.discount_method || '') || '—'}
              </strong>
              {typeof affiliate.discount_value === 'number' ? (
                <span>
                  {' '}
                  · Value: <strong style={{ color: UI.text }}>{affiliate.discount_value}</strong>
                </span>
              ) : null}
            </div>
          </div>
        ) : null}

        <div style={{ marginTop: 16, fontWeight: 800, color: UI.text, fontSize: 13 }}>
          Line items
        </div>
        <div style={{ marginTop: 8, borderTop: UI.border, paddingTop: 8 }}>
          {payments.length === 0 ? (
            <div style={{ color: UI.textMuted, fontSize: 13 }}>No payment rows returned.</div>
          ) : (
            payments.map((p, idx) => (
              <div
                key={idx + '-' + (p.payment_id || '')}
                style={{
                  display: 'grid',
                  gridTemplateColumns: '1fr 90px',
                  gap: 8,
                  padding: '8px 0',
                  borderBottom: '1px solid #ffe4f1',
                  fontSize: 13,
                }}
              >
                <div>
                  <div style={{ fontWeight: 700, color: UI.text }}>{p.payment_id}</div>
                  <div style={{ fontSize: 11, color: UI.textMuted }}>
                    {p.reference_type} #{p.reference_id} · qty {p.quantity}
                  </div>
                </div>
                <div style={{ textAlign: 'right', fontWeight: 800 }}>
                  {formatMoney(p.amount)}
                </div>
                <div style={{ gridColumn: '1 / -1', fontSize: 11, color: UI.textMuted }}>
                  Subtotal {formatMoney(p.subtotal)}
                  {Number(p.discount || 0) > 0 ? (
                    <span>
                      {' '}
                      · Discount −{formatMoney(p.discount)}
                    </span>
                  ) : null}
                </div>
              </div>
            ))
          )}
        </div>

        <div
          style={{
            marginTop: 16,
            borderTop: UI.border,
            paddingTop: 12,
            display: 'grid',
            gap: 6,
            fontSize: 13,
          }}
        >
          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
            <span style={{ color: UI.textMuted }}>Subtotal</span>
            <span style={{ fontWeight: 800 }}>{formatMoney(totals.subtotal)}</span>
          </div>
          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
            <span style={{ color: UI.textMuted }}>Discount</span>
            <span style={{ fontWeight: 800, color: '#047857' }}>
              − {formatMoney(totals.discount)}
            </span>
          </div>
          <div
            style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'baseline',
            }}
          >
            <span style={{ fontWeight: 900, fontSize: 15 }}>Total</span>
            <span style={{ fontWeight: 950, fontSize: 18, color: UI.primaryDark }}>
              {formatMoney(totals.total)}
            </span>
          </div>
          {totals.total_items !== undefined ? (
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span style={{ color: UI.textMuted }}>Total item units</span>
              <span style={{ fontWeight: 700 }}>{totals.total_items}</span>
            </div>
          ) : null}
        </div>

        <div style={{ marginTop: 16, display: 'flex', justifyContent: 'flex-end' }}>
          <Button
            onClick={() => {
              if (typeof window !== 'undefined') {
                window.print();
              }
            }}
            variant="ghost"
            style={{ marginRight: 8 }}
          >
            Print
          </Button>
          <Button onClick={onClose}>Done</Button>
        </div>
      </div>
    </div>
  );
}

function ExistingPlanConfirmModal({
  open,
  title,
  body,
  confirmLabel,
  cancelLabel,
  onConfirm,
  onCancel,
}) {
  if (!open) return null;
  return (
    <div
      style={{
        position: 'fixed',
        inset: 0,
        zIndex: 70,
        background: 'rgba(74,21,48,0.45)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 18,
        fontFamily: UI.font,
      }}
      role="dialog"
      aria-modal="true"
    >
      <div
        style={{
          width: 'min(560px, 100%)',
          borderRadius: UI.radiusLg,
          background: UI.surface,
          boxShadow: UI.shadow,
          border: UI.border,
          padding: 18,
        }}
      >
        <div style={{ fontWeight: 900, fontSize: 17, color: UI.text }}>{title}</div>
        <div style={{ marginTop: 10, color: UI.text, fontSize: 14, lineHeight: 1.55 }}>
          {body}
        </div>
        <div style={{ marginTop: 16, display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
          <Button variant="ghost" onClick={onCancel}>
            {cancelLabel || 'Cancel'}
          </Button>
          <Button onClick={onConfirm}>{confirmLabel || 'Continue checkout'}</Button>
        </div>
      </div>
    </div>
  );
}

function mergeCartLine(existing, incoming) {
  const q0 = Number(existing.quantity ?? 1);
  const q1 = Number(incoming.quantity ?? 1);
  return { ...existing, quantity: q0 + q1 };
}

function LoginPage({ onLoggedIn, pushToast }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [busy, setBusy] = useState(false);

  const submit = async (e) => {
    e.preventDefault();
    setBusy(true);
    try {
      await fetchSanctumCsrf();
      const data = await apiFetch('/login', {
        method: 'POST',
        body: JSON.stringify({ email: email.trim(), password }),
      });
      pushToast({
        type: 'success',
        title: 'Signed in',
        message: data && data.message ? data.message : 'Welcome back.',
      });
      onLoggedIn(data);
    } catch (err) {
      const msg =
        err &&
        err.payload &&
        (err.payload.message ||
          (err.payload.errors &&
            Object.values(err.payload.errors).flat().join(' '))) ||
        err.message ||
        'Login failed';
      pushToast({ type: 'error', title: 'Login failed', message: msg });
    } finally {
      setBusy(false);
    }
  };

  return (
    <div
      style={{
        minHeight: '100vh',
        background: UI.bg,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 18,
        fontFamily: UI.font,
      }}
    >
      <div
        style={{
          width: 'min(420px, 100%)',
          borderRadius: UI.radiusLg,
          background: UI.surface,
          border: UI.border,
          boxShadow: UI.shadow,
          padding: 22,
        }}
      >
        <div style={{ fontWeight: 950, fontSize: 22, color: UI.text }}>Clinic POS</div>
        <div style={{ marginTop: 6, color: UI.textMuted, fontSize: 13 }}>
          Sign in with your admin or cashier account.
        </div>
        <form onSubmit={submit} style={{ marginTop: 16 }}>
          <Field label="Email">
            <TextInput
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              autoComplete="username"
              inputMode="email"
              placeholder="name@clinic.com"
            />
          </Field>
          <Field label="Password">
            <TextInput
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              autoComplete="current-password"
            />
          </Field>
          <Button type="submit" disabled={busy || !email.trim() || !password} style={{ width: '100%' }}>
            {busy ? 'Signing in…' : 'Sign in'}
          </Button>
        </form>
      </div>
    </div>
  );
}

function POSPage({
  admin,
  onLogout,
  pushToast,
}) {
  const cartCounter = useRef(0);
  const [cart, setCart] = useState([]);
  const [catalogTab, setCatalogTab] = useState('product');
  const [catalogSearch, setCatalogSearch] = useState('');
  const debouncedSearch = useDebounce(catalogSearch, 340);
  const [catalogLoading, setCatalogLoading] = useState(false);
  const [catalog, setCatalog] = useState({ products: [], services: [], packages: [], memberships: [] });
  const [patientQuery, setPatientQuery] = useState('');
  const debouncedPatients = useDebounce(patientQuery, 260);
  const [patientsLoading, setPatientsLoading] = useState(false);
  const [patients, setPatients] = useState([]);
  const [patientId, setPatientId] = useState('');
  const [promotions, setPromotions] = useState([]);
  const [paymentMethod, setPaymentMethod] = useState('cash');
  const [paymentStatus, setPaymentStatus] = useState('paid');
  const [paymentDate, setPaymentDate] = useState('');
  const [transactionReference, setTransactionReference] = useState('');
  const [notes, setNotes] = useState('');
  const [affiliateInput, setAffiliateInput] = useState('');
  const [appliedAffiliate, setAppliedAffiliate] = useState(null);
  const [affiliateBusy, setAffiliateBusy] = useState(false);
  const [checkoutBusy, setCheckoutBusy] = useState(false);
  const [receiptOpen, setReceiptOpen] = useState(false);
  const [lastReceipt, setLastReceipt] = useState(null);
  const [confirmOpen, setConfirmOpen] = useState(false);
  const [confirmConfig, setConfirmConfig] = useState({
    title: '',
    body: '',
    onConfirm: null,
  });

  useEffect(() => {
    let cancelled = false;
    async function load() {
      setCatalogLoading(true);
      try {
        const data = await apiFetch(
          '/catalog?type=' +
            encodeURIComponent(catalogTab) +
            '&search=' +
            encodeURIComponent(debouncedSearch) +
            '&limit=120',
        );
        if (cancelled) return;
        setCatalog({
          products: data.products || [],
          services: data.services || [],
          packages: data.packages || [],
          memberships: data.memberships || [],
        });
      } catch (e) {
        if (!cancelled) {
          pushToast({
            type: 'error',
            title: 'Catalog error',
            message: e.message || 'Could not load catalog.',
          });
        }
      } finally {
        if (!cancelled) setCatalogLoading(false);
      }
    }
    load();
    return () => {
      cancelled = true;
    };
  }, [catalogTab, debouncedSearch, pushToast]);

  useEffect(() => {
    let cancelled = false;
    async function loadPatients() {
      setPatientsLoading(true);
      try {
        const data = await apiFetch(
          '/patients?search=' + encodeURIComponent(debouncedPatients) + '&limit=50',
        );
        if (cancelled) return;
        setPatients(data.patients || []);
      } catch (e) {
        if (!cancelled) {
          pushToast({
            type: 'error',
            title: 'Patients error',
            message: e.message || 'Could not load patients.',
          });
        }
      } finally {
        if (!cancelled) setPatientsLoading(false);
      }
    }
    loadPatients();
    return () => {
      cancelled = true;
    };
  }, [debouncedPatients, pushToast]);

  useEffect(() => {
    let cancelled = false;
    async function loadPromos() {
      try {
        const data = await apiFetch('/promotions');
        if (cancelled) return;
        setPromotions(data.promotions || []);
      } catch {
        if (!cancelled) setPromotions([]);
      }
    }
    loadPromos();
    return () => {
      cancelled = true;
    };
  }, []);

  const affiliateEligibleCartItems = useMemo(() => {
    return cart
      .filter((c) => ['product', 'service', 'package'].includes(c.type))
      .map((c) => ({
        type: c.type,
        id: Number(c.id),
        quantity: Number(c.quantity ?? 1),
        unit_price: Number(c.unit_price ?? c.price ?? 0),
      }));
  }, [cart]);

  const validateAffiliateCode = async (codeOverride) => {
    const raw = String(codeOverride != null ? codeOverride : affiliateInput).trim();
    if (!raw) {
      pushToast({
        type: 'error',
        title: 'Affiliate code required',
        message: 'Enter a code before applying.',
      });
      return;
    }
    setAffiliateBusy(true);
    try {
      const cartItems = affiliateEligibleCartItems;
      const bodyObject =
        cartItems.length === 0 ? { code: raw } : { code: raw, items: cartItems };
      const result = await apiFetch('/affiliate-codes/validate', {
        method: 'POST',
        body: JSON.stringify(bodyObject),
      });

      setAppliedAffiliate({
        affiliate_code: result.affiliate_code,
        items: result.items,
        totals: result.totals,
      });

      if (
        cartItems.length === 0 &&
        Array.isArray(result.items) &&
        result.items.length > 0
      ) {
        cartCounter.current = 0;
        const newCart = result.items.map((item) => {
          cartCounter.current += 1;
          return {
            ...item,
            _cartId: cartCounter.current,
            quantity: item.quantity ?? 1,
            unit_price: item.unit_price ?? item.price,
          };
        });
        setCart(newCart);
      }

      pushToast({
        type: 'success',
        title: 'Affiliate code applied',
        message: result.affiliate_code && result.affiliate_code.code
          ? result.affiliate_code.code + ' is active for this cart.'
          : 'Discount preview updated.',
      });
    } catch (e) {
      setAppliedAffiliate(null);
      const msg =
        (e.payload && e.payload.message) ||
        (e.payload &&
          e.payload.errors &&
          Object.values(e.payload.errors).flat().join(' ')) ||
        e.message ||
        'Validation failed';
      pushToast({ type: 'error', title: 'Affiliate code', message: msg });
    } finally {
      setAffiliateBusy(false);
    }
  };

  const addToCatalog = useCallback(
    (item) => {
      const unit = Number(item.price ?? item.unit_price ?? 0);
      const base = {
        ...item,
        unit_price: unit,
        price: unit,
        quantity: 1,
      };
      setCart((prev) => {
        const idx = prev.findIndex(
          (r) => r.type === base.type && Number(r.id) === Number(base.id),
        );
        if (idx === -1) {
          cartCounter.current += 1;
          return [...prev, { ...base, _cartId: cartCounter.current }];
        }
        const next = [...prev];
        next[idx] = mergeCartLine(next[idx], base);
        return next;
      });
    },
    [setCart],
  );

  const inc = useCallback((cartId) => {
    setCart((prev) =>
      prev.map((r) =>
        r._cartId === cartId ? { ...r, quantity: Number(r.quantity ?? 1) + 1 } : r,
      ),
    );
  }, []);

  const dec = useCallback((cartId) => {
    setCart((prev) =>
      prev
        .map((r) =>
          r._cartId === cartId
            ? { ...r, quantity: Math.max(1, Number(r.quantity ?? 1) - 1) }
            : r,
        )
        .filter((r) => Number(r.quantity ?? 1) > 0),
    );
  }, []);

  const remove = useCallback((cartId) => {
    setCart((prev) => prev.filter((r) => r._cartId !== cartId));
  }, []);

  const cartTotalsLocal = useMemo(() => {
    let subtotal = 0;
    let discount = 0;
    for (const row of cart) {
      const unit = Number(row.unit_price ?? row.price ?? 0);
      const qty = Number(row.quantity ?? 1);
      subtotal += unit * qty;
      discount += appliedAffiliate ? lineDiscountFor(appliedAffiliate, row) : 0;
    }
    const total = Math.max(0, subtotal - discount);
    return {
      subtotal,
      discount,
      total,
    };
  }, [cart, appliedAffiliate]);

  const logout = async () => {
    try {
      await apiFetch('/logout', { method: 'POST', body: JSON.stringify({}) });
    } catch {
      //
    }
    onLogout();
  };

  const affiliatePayloadForDiscount = appliedAffiliate;

  const performCheckout = async () => {
    const pid = Number(patientId);
    if (!pid) {
      pushToast({
        type: 'error',
        title: 'Patient required',
        message: 'Select a patient before checkout.',
      });
      return;
    }
    if (!cart.length) {
      pushToast({ type: 'error', title: 'Cart empty', message: 'Add items to the cart.' });
      return;
    }
    setCheckoutBusy(true);
    try {
      const items = cart.map((row) => ({
        type: row.type,
        id: Number(row.id),
        quantity: Number(row.quantity ?? 1),
        unit_price: Number(row.unit_price ?? row.price ?? 0),
      }));
      const payload = {
        patient_id: pid,
        payment_method: paymentMethod,
        payment_status: paymentStatus,
        items,
      };
      if (paymentDate.trim()) payload.payment_date = paymentDate.trim();
      if (transactionReference.trim()) payload.transaction_reference = transactionReference.trim();
      if (notes.trim()) payload.notes = notes.trim();
      if (appliedAffiliate && appliedAffiliate.affiliate_code && appliedAffiliate.affiliate_code.code) {
        payload.affiliate_code = String(appliedAffiliate.affiliate_code.code);
      }
      const result = await apiFetch('/checkout', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
      setLastReceipt(result);
      setReceiptOpen(true);
      setCart([]);
      setAppliedAffiliate(null);
      setAffiliateInput('');
      pushToast({
        type: 'success',
        title: 'Checkout complete',
        message: result.message || 'Sale recorded.',
      });
    } catch (e) {
      const msg =
        (e.payload && e.payload.message) ||
        (e.payload &&
          e.payload.errors &&
          Object.values(e.payload.errors).flat().join(' ')) ||
        e.message ||
        'Checkout failed';
      pushToast({ type: 'error', title: 'Checkout failed', message: msg });
    } finally {
      setCheckoutBusy(false);
    }
  };

  const handleCheckout = () => {
    const needsWarn = cart.some((c) => c.type === 'membership' || c.type === 'package');
    if (needsWarn) {
      setConfirmConfig({
        title: 'Confirm checkout',
        body:
          'This cart includes a membership and/or treatment package. Continuing will create subscriptions or packages for the selected patient. Verify the patient and items before proceeding.',
        onConfirm: async () => {
          setConfirmOpen(false);
          await performCheckout();
        },
      });
      setConfirmOpen(true);
      return;
    }
    return performCheckout();
  };

  const catalogList =
    catalogTab === 'product'
      ? catalog.products
      : catalogTab === 'service'
        ? catalog.services
        : catalogTab === 'package'
          ? catalog.packages
          : catalog.memberships;

  const selectedPatientLabel = useMemo(() => {
    if (!patientId) return '';
    const p = patients.find((x) => String(x.id) === String(patientId));
    return p ? p.name + (p.phone ? ' · ' + p.phone : '') : 'Patient #' + patientId;
  }, [patientId, patients]);

  return (
    <div
      style={{
        minHeight: '100vh',
        background: UI.bg,
        fontFamily: UI.font,
        color: UI.text,
      }}
    >
      <header
        style={{
          position: 'sticky',
          top: 0,
          zIndex: 40,
          background: 'rgba(255,255,255,0.88)',
          backdropFilter: 'blur(10px)',
          borderBottom: UI.border,
        }}
      >
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            padding: '12px 16px',
            gap: 12,
          }}
        >
          <div>
            <div style={{ fontWeight: 950, fontSize: 17, color: UI.text }}>Clinic POS</div>
            <div style={{ fontSize: 12, color: UI.textMuted }}>
              Cashier: {admin && admin.name ? admin.name : '—'}
              {admin && admin.email ? ' · ' + admin.email : ''}
            </div>
          </div>
          <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
            <Button variant="ghost" onClick={logout}>
              Log out
            </Button>
          </div>
        </div>
      </header>

      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'minmax(0,1.25fr) minmax(360px,1fr)',
          gap: 14,
          padding: 14,
          alignItems: 'start',
        }}
      >
        <section
          style={{
            borderRadius: UI.radiusLg,
            background: UI.surface,
            border: UI.border,
            boxShadow: UI.shadowSm,
            padding: 14,
          }}
        >
          <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 10 }}>
            {CATALOG_TABS.map((t) => (
              <Button
                key={t.key}
                variant={catalogTab === t.key ? 'primary' : 'ghost'}
                onClick={() => setCatalogTab(t.key)}
                style={{ padding: '6px 10px' }}
              >
                {t.label}
              </Button>
            ))}
          </div>
          <Field label="Search catalog">
            <TextInput
              value={catalogSearch}
              onChange={(e) => setCatalogSearch(e.target.value)}
              placeholder="Search name, SKU, brand…"
            />
          </Field>
          <div style={{ minHeight: 16, fontSize: 12, color: UI.textMuted }}>
            {catalogLoading ? 'Loading catalog…' : catalogList.length + ' results'}
          </div>
          <div
            style={{
              marginTop: 10,
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fill,minmax(210px,1fr))',
              gap: 10,
            }}
          >
            {catalogList.map((item) => (
              <CatalogCard key={item.type + '-' + item.id} item={item} tab={catalogTab} onAdd={addToCatalog} />
            ))}
          </div>
        </section>

        <aside
          style={{
            display: 'grid',
            gap: 12,
            position: 'sticky',
            top: 78,
          }}
        >
          <section
            style={{
              borderRadius: UI.radiusLg,
              background: UI.surface,
              border: UI.border,
              boxShadow: UI.shadowSm,
              padding: 14,
            }}
          >
            <div style={{ fontWeight: 900, fontSize: 14 }}>Patient</div>
            <div style={{ marginTop: 8 }}>
              <TextInput
                value={patientQuery}
                onChange={(e) => setPatientQuery(e.target.value)}
                placeholder="Search name, email, phone…"
              />
              <div style={{ marginTop: 8, fontSize: 12, color: UI.textMuted }}>
                {patientsLoading ? 'Searching patients…' : patients.length + ' matches'}
              </div>
              <SelectInput
                value={patientId}
                onChange={(e) => setPatientId(e.target.value)}
                style={{ marginTop: 8 }}
              >
                <option value="">Select patient…</option>
                {patients.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.name} · {p.email || 'no email'} · {p.phone || 'no phone'}
                  </option>
                ))}
              </SelectInput>
              {selectedPatientLabel ? (
                <div style={{ marginTop: 8, fontSize: 12, color: UI.textMuted }}>
                  Selected: {selectedPatientLabel}
                </div>
              ) : null}
            </div>
          </section>

          <section
            style={{
              borderRadius: UI.radiusLg,
              background: UI.surface,
              border: UI.border,
              boxShadow: UI.shadowSm,
              padding: 14,
            }}
          >
            <div style={{ fontWeight: 900, fontSize: 14 }}>Cart</div>
            <div style={{ marginTop: 10, display: 'grid', gap: 8 }}>
              {cart.length === 0 ? (
                <div style={{ color: UI.textMuted, fontSize: 13 }}>Cart is empty.</div>
              ) : (
                cart.map((row) => (
                  <CartItem
                    key={row._cartId}
                    row={row}
                    appliedAffiliate={affiliatePayloadForDiscount}
                    onInc={inc}
                    onDec={dec}
                    onRemove={remove}
                  />
                ))
              )}
            </div>
            <div
              style={{
                marginTop: 12,
                borderTop: UI.border,
                paddingTop: 12,
                display: 'grid',
                gap: 6,
                fontSize: 13,
              }}
            >
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span style={{ color: UI.textMuted }}>Subtotal</span>
                <span style={{ fontWeight: 800 }}>{formatMoney(cartTotalsLocal.subtotal)}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span style={{ color: UI.textMuted }}>Discount</span>
                <span style={{ fontWeight: 800, color: '#047857' }}>
                  − {formatMoney(cartTotalsLocal.discount)}
                </span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span style={{ fontWeight: 900 }}>Total</span>
                <span style={{ fontWeight: 950, fontSize: 16, color: UI.primaryDark }}>
                  {formatMoney(cartTotalsLocal.total)}
                </span>
              </div>
            </div>
          </section>

          <section
            style={{
              borderRadius: UI.radiusLg,
              background: UI.surface,
              border: UI.border,
              boxShadow: UI.shadowSm,
              padding: 14,
            }}
          >
            <div style={{ fontWeight: 900, fontSize: 14 }}>Affiliate code</div>
            <div style={{ marginTop: 8, display: 'flex', gap: 8 }}>
              <TextInput
                value={affiliateInput}
                onChange={(e) => setAffiliateInput(e.target.value.toUpperCase())}
                placeholder="ENTER CODE"
                style={{ flex: 1 }}
              />
              <Button disabled={affiliateBusy} onClick={() => validateAffiliateCode()}>
                {affiliateBusy ? 'Applying…' : 'Apply'}
              </Button>
            </div>
            <div style={{ marginTop: 8, display: 'flex', gap: 8 }}>
              <Button
                variant="ghost"
                onClick={() => {
                  setAppliedAffiliate(null);
                  setAffiliateInput('');
                  pushToast({
                    type: 'success',
                    title: 'Affiliate cleared',
                    message: 'Discount preview removed from the cart.',
                  });
                }}
              >
                Clear affiliate
              </Button>
            </div>
            {appliedAffiliate && appliedAffiliate.affiliate_code ? (
              <div style={{ marginTop: 10, fontSize: 12, color: UI.textMuted }}>
                Active: <strong style={{ color: UI.text }}>{appliedAffiliate.affiliate_code.code}</strong>
                {appliedAffiliate.affiliate_code.discount_label
                  ? ' · ' + appliedAffiliate.affiliate_code.discount_label
                  : ''}
              </div>
            ) : null}
          </section>

          <section
            style={{
              borderRadius: UI.radiusLg,
              background: UI.surface,
              border: UI.border,
              boxShadow: UI.shadowSm,
              padding: 14,
            }}
          >
            <div style={{ fontWeight: 900, fontSize: 14 }}>Payment</div>
            <div style={{ marginTop: 8, display: 'grid', gap: 10 }}>
              <Field label="Payment method">
                <SelectInput
                  value={paymentMethod}
                  onChange={(e) => setPaymentMethod(e.target.value)}
                >
                  {PAYMENT_METHODS.map((pm) => (
                    <option key={pm.value} value={pm.value}>
                      {pm.label}
                    </option>
                  ))}
                </SelectInput>
              </Field>
              <Field label="Payment status">
                <SelectInput
                  value={paymentStatus}
                  onChange={(e) => setPaymentStatus(e.target.value)}
                >
                  <option value="paid">paid</option>
                  <option value="unpaid">unpaid</option>
                  <option value="partial">partial</option>
                  <option value="refunded">refunded</option>
                  <option value="cancelled">cancelled</option>
                </SelectInput>
              </Field>
              <Field
                label="Payment date"
                hint="Optional. Defaults to today on the server when empty."
              >
                <TextInput
                  type="date"
                  value={paymentDate}
                  onChange={(e) => setPaymentDate(e.target.value)}
                />
              </Field>
              <Field label="Transaction reference">
                <TextInput
                  value={transactionReference}
                  onChange={(e) => setTransactionReference(e.target.value)}
                  placeholder="Optional external reference"
                />
              </Field>
              <Field label="Notes">
                <TextInput
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  placeholder="Optional notes for the sale"
                />
              </Field>
            </div>
            <div style={{ marginTop: 12 }}>
              <Button
                disabled={checkoutBusy}
                onClick={handleCheckout}
                style={{ width: '100%', padding: '11px 14px', fontSize: 14 }}
              >
                {checkoutBusy ? 'Processing…' : 'Checkout'}
              </Button>
            </div>
          </section>

          <section
            style={{
              borderRadius: UI.radiusLg,
              background: UI.surface,
              border: UI.border,
              boxShadow: UI.shadowSm,
              padding: 14,
            }}
          >
            <div style={{ fontWeight: 900, fontSize: 14 }}>Active promotions</div>
            <div style={{ marginTop: 8, display: 'grid', gap: 8, maxHeight: 260, overflow: 'auto' }}>
              {promotions.length === 0 ? (
                <div style={{ color: UI.textMuted, fontSize: 13 }}>No promotions loaded.</div>
              ) : (
                promotions.map((p) => (
                  <div
                    key={p.id}
                    style={{
                      borderRadius: 10,
                      border: '1px solid #ffe4f1',
                      background: '#fffafd',
                      padding: 10,
                    }}
                  >
                    <div style={{ fontWeight: 900, fontSize: 13 }}>{p.name}</div>
                    <div style={{ fontSize: 12, color: UI.textMuted, marginTop: 4 }}>
                      {p.discount_label || 'Promotion'}
                      {p.code ? ' · Code ' + p.code : ''}
                    </div>
                    <div style={{ fontSize: 11, color: UI.textMuted, marginTop: 6 }}>
                      Applies to: {p.applies_to || '—'}
                    </div>
                  </div>
                ))
              )}
            </div>
          </section>
        </aside>
      </div>

      <ReceiptModal
        open={receiptOpen}
        onClose={() => setReceiptOpen(false)}
        receipt={lastReceipt}
      />

      <ExistingPlanConfirmModal
        open={confirmOpen}
        title={confirmConfig.title}
        body={confirmConfig.body}
        onCancel={() => setConfirmOpen(false)}
        onConfirm={confirmConfig.onConfirm || (() => setConfirmOpen(false))}
        confirmLabel="Continue"
        cancelLabel="Back"
      />
    </div>
  );
}

function App() {
  const { toasts, pushToast, dismiss } = useToast();
  const [ready, setReady] = useState(false);
  const [admin, setAdmin] = useState(null);

  useEffect(() => {
    let cancelled = false;
    async function boot() {
      try {
        const data = await apiFetch('/me');
        if (cancelled) return;
        if (data && data.admin && data.admin.id) {
          setAdmin(data.admin);
        } else {
          setAdmin(null);
        }
      } catch {
        if (!cancelled) setAdmin(null);
      } finally {
        if (!cancelled) setReady(true);
      }
    }
    boot();
    return () => {
      cancelled = true;
    };
  }, []);

  if (!ready) {
    return (
      <div
        style={{
          minHeight: '100vh',
          background: UI.bg,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontFamily: UI.font,
          color: UI.textMuted,
        }}
      >
        Loading POS…
      </div>
    );
  }

  return (
    <div style={{ position: 'relative' }}>
      {!admin ? (
        <LoginPage
          onLoggedIn={async (payload) => {
            try {
              const data = await apiFetch('/me');
              if (data && data.admin && data.admin.id) {
                setAdmin(data.admin);
                return;
              }
            } catch {
              //
            }
            if (payload && payload.admin) {
              setAdmin(payload.admin);
            } else {
              setAdmin({ name: 'Cashier' });
            }
          }}
          pushToast={pushToast}
        />
      ) : (
        <POSPage admin={admin} onLogout={() => setAdmin(null)} pushToast={pushToast} />
      )}

      <div
        style={{
          position: 'fixed',
          right: 16,
          bottom: 16,
          zIndex: 80,
          width: 'min(440px,calc(100vw - 32px))',
        }}
      >
        {toasts.map((t) => (
          <Toast key={t.id} toast={t} onDismiss={dismiss} />
        ))}
      </div>
    </div>
  );
}

export default App;
