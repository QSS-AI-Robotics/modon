<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center bg-primary text-white">
                    <h4>Signup</h4>
                </div>
                <div class="card-body">
                    <form id="signupForm">
                        @csrf <!-- Ensure CSRF token is available -->
                        
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                            <span class="text-danger" id="fullname_error"></span>
                        </div>
        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <span class="text-danger" id="email_error"></span>
                        </div>
        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span class="text-danger" id="password_error"></span>
                        </div>
        
                        <div class="mb-3">
                            <label for="region" class="form-label">Region</label>
                            <select class="form-select" id="region" name="region" required>
                                <option value="">Select Region</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="region_error"></span>
                        </div>
        
                        <div class="mb-3">
                            <label for="user_type" class="form-label">User Type</label>
                            <select class="form-select" id="user_type" name="user_type" required>
                                <option value="">Select User Type</option>
                                @foreach($userTypes as $userType)
                                    <option value="{{ $userType->id }}">{{ $userType->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="user_type_error"></span>
                        </div>
        
                        <button type="submit" class="btn btn-primary w-100">Signup</button>
                    </form>
                    <div id="successMessage" class="alert alert-success mt-3 d-none"></div>
        
                    <!-- Sign-in Link -->
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="{{ route('signin.form') }}" class="text-primary">Sign in here</a></p>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/modon.js') }}"></script> <!-- ✅ Include External JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
