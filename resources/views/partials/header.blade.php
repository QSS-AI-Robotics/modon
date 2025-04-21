        
        @php
            $navLinks = Auth::user()?->userType?->navigationLinks()->orderBy('sort_order')->get();
        @endphp
        <!-- Header -->
        <div class="row header shadows bg-section p-1 mb-2 align-items-center">
            
            <div class="col-2 d-flex align-items-center">
                <img src="{{ asset('images/qss.png') }}" alt="Logo" class="w-50">
                <img src="{{ asset('images/smodon.png') }}" alt="Logo" class="img-fluid px-2 mb-1" style="width:32%">
            </div>
            <div class="col-7 d-flex">
                @foreach($navLinks as $link)
                    @php
                        $isActive = $link->url === '/'
                            ? request()->is('/')
                            : request()->is(ltrim($link->url, '/') . '*');
                    @endphp
                    <a href="{{ $link->url }}" class="btn cont-btn mx-1 {{ $isActive ? 'selected' : '' }}">
                        @if($link->icon)
                            <img src="{{ asset('images/' . $link->icon) }}" alt="{{ $link->name }}" class="" style="width:15px">
                        @endif
                        {{ $link->name }}
                    </a>
                @endforeach
            </div>
            
            
            <div class="col-3 d-flex justify-content-end">

                <div class="profile-wrapper">
                    <div class="profile-toggle" id="profileToggle">
                        <div class="profile-info">
                            <div class="fw-bold text-capitalize">{{ Auth::user()->name ?? 'Admin' }}</div>
                            <div class="small text-white">{{ Auth::user()->email ?? 'email@example.com' }}</div>
                            <input type="hidden" id="passwordResetEnable" value="{{ Auth::user()->force_password_reset }}"/>
                            
                        </div>
                        <img 
                            src="{{ asset('storage/users/' . (Auth::user()->image ?? 'user.png')) }}" 
                            alt="Profile" 
                            class="profile-img"
                        >
                    </div>
                    
                
                    <div class="profile-dropdown" id="profileDropdown">
                        <button type="button" class="btn btn-sm text-white w-100 mb-2 d-flex align-items-center" id="editProfileButton" style="background: #105A7E">
                            <img src="{{ asset('images/people.png') }}" alt="Profile Icon" class="me-4" style="width: 20px; height: 20px;">
                            <span>Edit Profile</span>
                        </button>
                        <button type="submit" class="btn btn-sm btn-danger w-100 text-start" id="logoutButton">
                            <img src="{{ asset('images/logout.png') }}" alt="Profile Icon" class="me-4" style="width: 20px; height: 20px;">
                            <span>Logout</span>
                        </button>
                           
                            {{-- <button type="submit" class="btn btn-sm btn-danger w-100" id="logoutButton">Logout</button> --}}
                    
                    </div>
                </div>
                
                
                
            </div>
            <!-- Password Reset Modal -->
            <div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background: #105A7E">
                        <div class="modal-header text-white" >
                            <h5 class="modal-title text-white" id="passwordResetModalLabel">Reset Password</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-white">
                            <form id="passwordResetForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <input type="password" class="form-control  dateInput" id="currentPassword" name="currentPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control e dateInput" id="newPassword" name="newPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control  dateInput" id="confirmNewPassword" name="newPassword_confirmation" required>
                                </div>
                                <button type="submit" class="btn text-center passbtn mission-btn " >Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        @push('scripts')
            <script src="{{ asset('js/custom.js') }}"></script>

        @endpush