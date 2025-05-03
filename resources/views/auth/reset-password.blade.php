<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Gradient nền toàn trang */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card-reset {
            max-width: 900px;
            border: none;
            border-radius: .75rem;
            overflow: hidden;
        }
        .card-reset .bg-image {
            background: url('{{ asset("images/reset-bg.jpg") }}') center/cover no-repeat;
        }
    </style>
</head>
<body>
<div class="d-flex align-items-center justify-content-center py-5">
    <div class="card card-reset shadow-lg">
        <div class="row g-0">
            <!-- Cột Ảnh / Thông điệp (ẩn trên mobile) -->
            <div class="col-md-6 d-none d-md-flex flex-column align-items-center justify-content-center text-white p-4 bg-image">
                <h1 class="display-4">🔒</h1>
                <h3 class="mb-3">Đặt lại mật khẩu</h3>
                <p class="text-center px-3">Nhập mật khẩu mới để bảo vệ tài khoản của bạn.</p>
            </div>
            <!-- Cột Form -->
            <div class="col-md-6 p-4 p-md-5 bg-white">
                <!-- Thông báo thành công -->
                @if (session('status'))
                    <div class="alert alert-success text-center">
                        {{ session('status') }}
                    </div>
                @endif

                <h4 class="mb-4 text-center">Đặt lại mật khẩu của bạn</h4>

                <form action="{{ url('password/reset') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

                    {{-- Mật khẩu mới --}}
                    <div class="form-floating mb-3">
                        <input
                          type="password"
                          class="form-control @error('password') is-invalid @enderror"
                          id="password"
                          name="password"
                          placeholder="Mật khẩu mới"
                          required>
                        <label for="password">Mật khẩu mới</label>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Xác nhận mật khẩu --}}
                    <div class="form-floating mb-4">
                        <input
                          type="password"
                          class="form-control"
                          id="password_confirmation"
                          name="password_confirmation"
                          placeholder="Xác nhận mật khẩu"
                          required>
                        <label for="password_confirmation">Xác nhận mật khẩu</label>
                    </div>

                    {{-- Nút submit --}}
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                        Đặt lại mật khẩu
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ url('/') }}" class="text-decoration-none text-secondary">
                        🔙 Quay lại trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS (tuỳ chọn) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
