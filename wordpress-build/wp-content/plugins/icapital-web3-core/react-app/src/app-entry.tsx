/**
 * app-entry.tsx — WordPress React App Entry Point
 *
 * Reads the data-page attribute from <div id="icapital-app">
 * and mounts the appropriate component.
 * window.icapitalData is injected by WordPress via wp_localize_script().
 */
import React from 'react';
import { createRoot } from 'react-dom/client';

// Re-use existing components from the Next.js source
// (copy src/ files into this react-app/src/ directory, or symlink them)
// The API fetch URLs are replaced with window.icapitalData.restUrl

declare global {
  interface Window {
    icapitalData: {
      restUrl:     string;
      nonce:       string;
      isLoggedIn:  boolean;
      currentUser: null | {
        id:          number;
        email:       string;
        displayName: string;
        firstName:   string;
        lastName:    string;
      };
      loginUrl: string;
      siteUrl:  string;
    };
  }
}

/** Thin API client that uses WP REST nonce instead of JWT */
export const api = {
  get: (path: string) =>
    fetch(`${window.icapitalData.restUrl}${path}`, {
      headers: { 'X-WP-Nonce': window.icapitalData.nonce },
      credentials: 'include',
    }).then(r => r.json()),

  post: (path: string, body: unknown) =>
    fetch(`${window.icapitalData.restUrl}${path}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.icapitalData.nonce,
      },
      credentials: 'include',
      body: JSON.stringify(body),
    }).then(r => r.json()),
};

// ── Lazy-load app components ──────────────────────────────────
const DashboardApp    = React.lazy(() => import('./apps/DashboardApp'));
const SecureAssetApp  = React.lazy(() => import('./apps/SecureAssetApp'));
const AdminApp        = React.lazy(() => import('./apps/AdminApp'));

function AppRouter() {
  const container = document.getElementById('icapital-app');
  const page = container?.dataset?.page ?? 'dashboard';

  return (
    <React.Suspense fallback={
      <div style={{display:'flex',alignItems:'center',justifyContent:'center',minHeight:'60vh'}}>
        <div style={{width:40,height:40,border:'3px solid #2563eb',borderTopColor:'transparent',borderRadius:'50%',animation:'spin 0.8s linear infinite'}}/>
        <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
      </div>
    }>
      {page === 'dashboard'     && <DashboardApp   api={api} data={window.icapitalData} />}
      {page === 'secure-asset'  && <SecureAssetApp api={api} data={window.icapitalData} />}
      {page === 'admin'         && <AdminApp        api={api} data={window.icapitalData} />}
    </React.Suspense>
  );
}

// Mount
const container = document.getElementById('icapital-app');
if (container) {
  createRoot(container).render(
    <React.StrictMode>
      <AppRouter />
    </React.StrictMode>
  );
}
