{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Login — Fast Services Payroll')

@section('content')
<div style="display:flex;min-height:100vh;">

    {{-- ── Left panel (form) ────────────────────────────────── --}}
    <div style="flex:1;display:flex;flex-direction:column;justify-content:center;
                padding:60px 80px;max-width:520px;background:#111111;">

        {{-- Brand --}}
        <div style="margin-bottom:48px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="line-height:1.1;">
                    <div style="color:#C9A227;font-weight:700;font-size:.95rem;letter-spacing:.05em;">
                        CLDG OFFICE
                    </div>
                    <div style="color:rgba(255,255,255,.4);font-size:.7rem;letter-spacing:.08em;">
                        PAYROLL SYSTEM
                    </div>
                </div>
            </div>
        </div>

        {{-- Heading --}}
        <div style="margin-bottom:32px;">
            <h1 style="color:#ffffff;font-size:1.75rem;font-weight:700;margin:0 0 6px;">
                Welcome back
            </h1>
            <p style="color:rgba(255,255,255,.4);font-size:.875rem;margin:0;">
                Sign in to your account to continue
            </p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Username --}}
            <div style="margin-bottom:20px;">
                <label for="username"
                       style="display:block;color:rgba(255,255,255,.7);
                              font-size:.8rem;font-weight:500;margin-bottom:8px;
                              letter-spacing:.04em;">
                    USERNAME
                </label>
                <input type="text"
                       id="username"
                       name="username"
                       value="{{ old('username') }}"
                       autocomplete="username"
                       placeholder="Enter your username"
                       autofocus
                       style="width:100%;height:46px;background:#1a1a1a;
                              border:1px solid {{ $errors->has('username') ? '#C9A227' : '#2a2a2a' }};
                              border-radius:6px;padding:0 14px;color:#fff;
                              font-size:.9rem;box-sizing:border-box;outline:none;"
                       onfocus="this.style.borderColor='#C9A227'"
                       onblur="this.style.borderColor='{{ $errors->has('username') ? '#C9A227' : '#2a2a2a' }}'">
                @error('username')
                    <span style="color:#C9A227;font-size:.78rem;margin-top:5px;display:block;">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            {{-- Password --}}
            <div style="margin-bottom:28px;">
                <label for="password"
                       style="display:block;color:rgba(255,255,255,.7);
                              font-size:.8rem;font-weight:500;margin-bottom:8px;
                              letter-spacing:.04em;">
                    PASSWORD
                </label>
                <div style="position:relative;">
                    <input type="password"
                           id="password"
                           name="password"
                           autocomplete="current-password"
                           placeholder="Enter your password"
                           style="width:100%;height:46px;background:#1a1a1a;
                                  border:1px solid {{ $errors->has('password') ? '#C9A227' : '#2a2a2a' }};
                                  border-radius:6px;padding:0 44px 0 14px;color:#fff;
                                  font-size:.9rem;box-sizing:border-box;outline:none;"
                           onfocus="this.style.borderColor='#C9A227'"
                           onblur="this.style.borderColor='{{ $errors->has('password') ? '#C9A227' : '#2a2a2a' }}'">
                    {{-- Toggle visibility --}}
                    <button type="button"
                            onclick="togglePassword()"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                   background:none;border:none;cursor:pointer;padding:0;
                                   color:rgba(255,255,255,.3);">
                        <i id="pwIcon" class="bi bi-eye" style="font-size:1rem;"></i>
                    </button>
                </div>
                @error('password')
                    <span style="color:#C9A227;font-size:.78rem;margin-top:5px;display:block;">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit"
                    style="width:100%;height:46px;background:#C9A227;border:none;
                           border-radius:6px;color:#0d0d0d;font-size:.95rem;
                           font-weight:700;cursor:pointer;letter-spacing:.03em;
                           transition:background .2s;"
                    onmouseover="this.style.background='#a8841e'"
                    onmouseout="this.style.background='#C9A227'">
                Sign In
            </button>

        </form>

    </div>

<div style="flex:1; position:relative; overflow:hidden;
            background:url('{{ asset('img/background.jpg') }}') no-repeat center center/cover;
            display:flex; flex-direction:column;
            align-items:center; justify-content:center; padding:40px;
            border-left:1px solid #1f1f1f;">

    {{-- Dark blur overlay --}}
    <div style="position:absolute; inset:0;
                background:rgba(0,0,0,0.55);
                backdrop-filter:blur(4px);
                -webkit-backdrop-filter:blur(4px);
                z-index:0;">
    </div>

    {{-- Logo (must be above overlay) --}}
    <img src="{{ asset('img/logo.jpg') }}"
         alt="CLDG Logo"
         style="position:relative; z-index:1;
                width:300px; height:300px; object-fit:contain;
                border-radius:50%; margin-bottom:28px;
                border:3px solid #C9A227;">
</div>

</div>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const pw   = document.getElementById('password');
        const icon = document.getElementById('pwIcon');
        if (pw.type === 'password') {
            pw.type       = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            pw.type       = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    // Flash session errors via SweetAlert
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: '{{ session('error') }}',
            background: '#1a1a1a',
            color: '#fff',
            confirmButtonColor: '#C9A227',
        });
    @endif
</script>
@endpush