<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $mode ?? 'Sign Up' }} — MyFirstApp</title>
<style>
  :root {
    --bg: #0b0b0f;
    --panel: #ffffff;
    --field: #1a1a22;
    --field-border: #2a2a35;
    --text: #f2f2f5;
    --muted: #9a9aa8;
    --violet-1: #6d28d9;
    --violet-2: #a855f7;
    --violet-3: #2e1065;
    --danger: #f87171;
    --radius: 14px;
    font-family: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px;
  }

  .shell {
    width: 100%;
    max-width: 980px;
    min-height: 640px;
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    background: #101014;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 40px 80px -30px rgba(0,0,0,0.6);
    border: 1px solid #1c1c24;
  }

  /* LEFT PANEL */
  .side {
    position: relative;
    background:
      radial-gradient(circle at 30% 20%, var(--violet-2) 0%, transparent 45%),
      radial-gradient(circle at 70% 80%, var(--violet-1) 0%, transparent 50%),
      linear-gradient(160deg, var(--violet-3), #0b0b0f 70%);
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
  }

  .brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #e9e4ff;
    opacity: 0.85;
  }

  .brand-logo {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: #fff;
  }

  .side-copy {
    text-align: left;
  }

  .side-copy h1 {
    font-size: 30px;
    line-height: 1.2;
    margin: 0 0 10px;
    font-weight: 700;
    letter-spacing: -0.02em;
  }

  .side-copy p {
    margin: 0;
    color: #d9d3ee;
    font-size: 14.5px;
    line-height: 1.5;
    max-width: 340px;
  }

  /* RIGHT PANEL */
  .form-side {
    padding: 48px 56px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 700px;
  }

  .form-head {
    text-align: center;
    margin-bottom: 28px;
  }

  .form-head h2 {
    margin: 0 0 6px;
    font-size: 26px;
    font-weight: 700;
    letter-spacing: -0.01em;
  }

  .form-head p {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
  }

  .oauth-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
  }

  .oauth-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 12px;
    background: var(--field);
    border: 1px solid var(--field-border);
    border-radius: 10px;
    color: var(--text);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: border-color 0.15s ease, transform 0.1s ease;
  }

  .oauth-btn:hover { border-color: #46465a; }
  .oauth-btn:active { transform: scale(0.98); }
  .oauth-btn svg { width: 17px; height: 17px; }

  .divider {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--muted);
    font-size: 12.5px;
    margin: 4px 0 20px;
  }

  .divider::before, .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--field-border);
  }

  form { display: flex; flex-direction: column; gap: 16px; }

  .name-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .field label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 6px;
    color: #d8d8e2;
  }

  .field input {
    width: 100%;
    padding: 11px 14px;
    background: var(--field);
    border: 1px solid var(--field-border);
    border-radius: 10px;
    color: var(--text);
    font-size: 14px;
    outline: none;
    transition: border-color 0.15s ease, background 0.15s ease;
  }

  .field input::placeholder { color: #5c5c6b; }

  .field input:focus {
    border-color: var(--violet-2);
    background: #1e1e28;
  }

  .field-hint {
    font-size: 12px;
    color: var(--muted);
    margin-top: 5px;
  }

  .field-error {
    font-size: 12px;
    color: var(--danger);
    margin-top: 5px;
    display: none;
  }

  .field.invalid input { border-color: var(--danger); }
  .field.invalid .field-error { display: block; }
  .field.invalid .field-hint { display: none; }

  .row-between {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
  }

  .remember {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--muted);
  }

  .remember input { accent-color: var(--violet-2); }

  .link {
    color: #c9b8ff;
    text-decoration: none;
    font-weight: 500;
  }

  .link:hover { text-decoration: underline; }

  .submit-btn {
    margin-top: 4px;
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 10px;
    background: var(--text);
    color: #0b0b0f;
    font-size: 14.5px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.15s ease, transform 0.1s ease;
  }

  .submit-btn:hover { opacity: 0.9; }
  .submit-btn:active { transform: scale(0.99); }

  .switch-line {
    text-align: center;
    font-size: 13.5px;
    color: var(--muted);
    margin-top: 22px;
  }

  .switch-line button {
    background: none;
    border: none;
    color: var(--text);
    font-weight: 700;
    cursor: pointer;
    font-size: 13.5px;
    padding: 0;
  }

  .panel { display: none; }
  .panel.active { display: flex; flex-direction: column; gap: 16px; }

  @media (max-width: 860px) {
    .shell { grid-template-columns: 1fr; }
    .side { display: none; }
    .form-side { padding: 40px 28px; height: auto; }
  }
</style>
</head>
<body>

<div class="shell">
  <!-- LEFT / BRAND PANEL -->
  <div class="side">
    <div class="brand">
      <div class="brand-logo">MA</div>
      <span>MyApp</span>
    </div>

    <div class="side-copy">
      <h1 id="sideHeadline">Get Started with Us</h1>
      <p id="sideSub">Complete the steps to register your account</p>
    </div>

    <div></div>
  </div>

  <!-- RIGHT / FORM PANEL -->
  <div class="form-side">

    <!-- SIGN UP FORM -->
    <div class="panel active" id="signupPanel">
      <div class="form-head">
        <h2>Sign Up Account</h2>
        <p>Enter your personal data to create your account</p>
      </div>

      <div class="oauth-row">
        <button type="button" class="oauth-btn" onclick="window.location.href='{{ url('/auth/google') }}'">
          <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.2-2.27H12v4.51h6.47c-.29 1.48-1.14 2.73-2.43 3.58v3h3.93c2.3-2.12 3.52-5.24 3.52-8.82z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.93-3c-1.09.73-2.48 1.16-4 1.16-3.08 0-5.68-2.08-6.61-4.87H1.34v3.09C3.31 21.3 7.34 24 12 24z"/><path fill="#FBBC05" d="M5.39 14.38c-.24-.73-.38-1.5-.38-2.38s.14-1.65.38-2.38V6.53H1.34C.49 8.2 0 10.05 0 12s.49 3.8 1.34 5.47l4.05-3.09z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.45-3.45C17.95 1.19 15.24 0 12 0 7.34 0 3.31 2.7 1.34 6.53l4.05 3.09C6.32 6.83 8.92 4.75 12 4.75z"/></svg>
          Google
        </button>
        <button type="button" class="oauth-btn" onclick="window.location.href='{{ url('/auth/github') }}'">
          <svg viewBox="0 0 16 16" fill="currentColor"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
          Github
        </button>
      </div>

      <div class="divider">Or</div>

      @if ($errors->any())
        <div style="background:#3b1a1a; border:1px solid #7a2b2b; color:#f3b8b8; padding:10px 14px; border-radius:10px; font-size:13px; margin-bottom:4px;">
          {{ $errors->first() }}
        </div>
      @endif

      <form id="signupForm" method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="name-row">
          <div class="field" id="field-first_name">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" placeholder="eg.John" value="{{ old('first_name') }}">
            <div class="field-error">Please enter your first name.</div>
          </div>
          <div class="field" id="field-last_name">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" placeholder="eg.Francisco" value="{{ old('last_name') }}">
            <div class="field-error">Please enter your last name.</div>
          </div>
        </div>

        <div class="field" id="field-email">
          <label for="signup_email">Email</label>
          <input type="email" id="signup_email" name="email" placeholder="eg.Johnfranc@gmail.com" value="{{ old('email') }}">
          <div class="field-error">Please enter a valid email address.</div>
        </div>

        <div class="field" id="field-password">
          <label for="signup_password">Password</label>
          <input type="password" id="signup_password" name="password" placeholder="Enter your password">
          <div class="field-hint">Must be at least 8 characters</div>
          <div class="field-error">Password must be at least 8 characters.</div>
        </div>

        <button type="submit" class="submit-btn">Sign Up</button>
      </form>

      <div class="switch-line">
        Already have an Account ? <button type="button" id="goToLogin">Log in</button>
      </div>
    </div>

    <!-- SIGN IN FORM -->
    <div class="panel" id="loginPanel">
      <div class="form-head">
        <h2>Log In</h2>
        <p>Welcome back, enter your details to sign in</p>
      </div>

      <div class="oauth-row">
        <button type="button" class="oauth-btn" onclick="window.location.href='{{ url('/auth/google') }}'">
          <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.2-2.27H12v4.51h6.47c-.29 1.48-1.14 2.73-2.43 3.58v3h3.93c2.3-2.12 3.52-5.24 3.52-8.82z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.93-3c-1.09.73-2.48 1.16-4 1.16-3.08 0-5.68-2.08-6.61-4.87H1.34v3.09C3.31 21.3 7.34 24 12 24z"/><path fill="#FBBC05" d="M5.39 14.38c-.24-.73-.38-1.5-.38-2.38s.14-1.65.38-2.38V6.53H1.34C.49 8.2 0 10.05 0 12s.49 3.8 1.34 5.47l4.05-3.09z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.45-3.45C17.95 1.19 15.24 0 12 0 7.34 0 3.31 2.7 1.34 6.53l4.05 3.09C6.32 6.83 8.92 4.75 12 4.75z"/></svg>
          Google
        </button>
        <button type="button" class="oauth-btn" onclick="window.location.href='{{ url('/auth/github') }}'">
          <svg viewBox="0 0 16 16" fill="currentColor"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
          Github
        </button>
      </div>

      <div class="divider">Or</div>

      <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <div class="field" id="field-login_email">
          <label for="login_email">Email</label>
          <input type="email" id="login_email" name="email" placeholder="eg.Johnfranc@gmail.com">
          <div class="field-error">Please enter a valid email address.</div>
        </div>

        <div class="field" id="field-login_password">
          <label for="login_password">Password</label>
          <input type="password" id="login_password" name="password" placeholder="Enter your password">
          <div class="field-error">Please enter your password.</div>
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox" name="remember">
            Remember me
          </label>
          <a href="{{ url('/forgot-password') }}" class="link">Forgot password?</a>
        </div>

        <button type="submit" class="submit-btn">Log In</button>
      </form>

      <div class="switch-line">
        Don't have an account ? <button type="button" id="goToSignup">Sign up</button>
      </div>
    </div>

  </div>
</div>

<script>
  const signupPanel = document.getElementById('signupPanel');
  const loginPanel = document.getElementById('loginPanel');
  const sideHeadline = document.getElementById('sideHeadline');
  const sideSub = document.getElementById('sideSub');

  function showSignup() {
    loginPanel.classList.remove('active');
    signupPanel.classList.add('active');
    sideHeadline.textContent = 'Get Started with Us';
    sideSub.textContent = 'Complete the steps to register your account';
  }

  function showLogin() {
    signupPanel.classList.remove('active');
    loginPanel.classList.add('active');
    sideHeadline.textContent = 'Welcome Back';
    sideSub.textContent = 'Log in to continue where you left off';
  }

  document.getElementById('goToLogin').addEventListener('click', showLogin);
  document.getElementById('goToSignup').addEventListener('click', showSignup);

  // --- Simple client-side validation ---
  function setInvalid(fieldId, invalid) {
    const el = document.getElementById(fieldId);
    if (!el) return;
    el.classList.toggle('invalid', invalid);
  }

  function isEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  document.getElementById('signupForm').addEventListener('submit', function (e) {
    let valid = true;

    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const email = document.getElementById('signup_email').value.trim();
    const password = document.getElementById('signup_password').value;

    setInvalid('field-first_name', firstName.length === 0);
    setInvalid('field-last_name', lastName.length === 0);
    setInvalid('field-email', !isEmail(email));
    setInvalid('field-password', password.length < 8);

    if (firstName.length === 0 || lastName.length === 0 || !isEmail(email) || password.length < 8) {
      valid = false;
    }

    if (!valid) e.preventDefault();
  });

  document.getElementById('loginForm').addEventListener('submit', function (e) {
    let valid = true;

    const email = document.getElementById('login_email').value.trim();
    const password = document.getElementById('login_password').value;

    setInvalid('field-login_email', !isEmail(email));
    setInvalid('field-login_password', password.length === 0);

    if (!isEmail(email) || password.length === 0) {
      valid = false;
    }

    if (!valid) e.preventDefault();
  });
</script>

</body>
</html>