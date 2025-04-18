        
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
                        </div>
                        <img 
                            src="{{ asset('storage/users/' . (Auth::user()->image ?? 'user.png')) }}" 
                            alt="Profile" 
                            class="profile-img"
                        >
                    </div>
                    
                
                    <div class="profile-dropdown" id="profileDropdown">
                       
                           
                            <button type="submit" class="btn btn-sm btn-danger w-100" id="logoutButton">Logout</button>
                    
                    </div>
                </div>
                
                
            </div>
            
        </div>