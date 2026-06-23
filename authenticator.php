<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Authenticator | Star Oil Fuel Voucher System</title>
    <link rel="preconnect" href="https://fonts.bunny.net" /><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script>
  </head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-4 py-6">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-soft" aria-labelledby="authenticator-title">
        <a class="mb-4 inline-flex text-sm font-semibold text-brand-blue" href="signin">Back to login</a>
        <h1 id="authenticator-title" class="text-2xl font-bold">Google Authenticator</h1>
        <p class="mt-2 text-sm leading-6 text-brand-muted">Set up or verify the authenticator app used for secure voucher operations.</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:items-center">
          <div class="mx-auto grid h-36 w-36 grid-cols-5 gap-1 rounded-ui border border-brand-line bg-white p-3 shadow-sm" aria-label="Authenticator QR code preview"><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-blue"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span><span></span><span class="rounded-sm bg-brand-ink"></span></div>
          <div class="rounded-ui border border-brand-line bg-brand-soft p-3"><p class="text-sm font-semibold">Manual setup key</p><p class="mt-2 break-all rounded-ui bg-white px-3 py-2 font-mono text-sm ring-1 ring-brand-line">STAR-OIL-2026-VOUCHER</p></div>
        </div>
        <form class="mt-5 space-y-4" data-auth-form data-next="store" data-code="true">
          <label class="block"><span class="text-sm font-medium text-brand-muted">Authenticator code</span><input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-3 text-center text-2xl font-bold tracking-[0.35em] focus:outline-none focus:ring-2 focus:ring-brand-blue" type="text" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required /></label>
          <button class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F]" type="submit">Verify Authenticator</button>
        </form>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-4 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script src="assets/app.js"></script>
  </body>
</html>
