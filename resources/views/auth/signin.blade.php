<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center bg-primary text-white">
                    <h4>Sign In</h4>
                </div>
                <div class="card-body">
                    <form id="signinForm">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="bilal@qltyss.com" required>
                            <span class="text-danger" id="email_error"></span>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" value="admin1234" required>
                            <span class="text-danger" id="password_error"></span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Sign In</button>
                    </form>
                    <div id="errorMessage" class="alert alert-danger mt-3 d-none"></div>

                    <!-- Signup Link -->
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="{{ route('signup.form') }}" class="text-primary">Sign up here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/modon.js') }}"></script> <!-- Load External JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
