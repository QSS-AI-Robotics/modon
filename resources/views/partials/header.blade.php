        
        @php
            $navLinks = Auth::user()?->userType?->navigationLinks()->orderBy('sort_order')->get();
        @endphp
        <!-- Header -->
        <div class="row header shadows bg-section p-1 mb-2 align-items-center">
            <div class="col-2 d-flex align-items-center">
                <img src="{{ asset('images/qss.png') }}" alt="Logo" class="w-50">
            </div>
            <div class="col-7 d-flex">

                @foreach($navLinks as $link)
                    @php
                        $isActive = $link->url === '/'
                            ? request()->is('/')
                            : request()->is(ltrim($link->url, '/') . '*');
                    @endphp
                    <a href="{{ $link->url }}" class="btn cont-btn mx-1 {{ $isActive ? 'selected' : '' }}">
                        {{ $link->name }}
                    </a>
                @endforeach

                {{-- @foreach($navLinks as $link)
                    <a href="{{ $link->url }}" class="btn cont-btn mx-1 {{ request()->is(ltrim($link->url, '/') . '*') ? 'selected' : '' }}">
                        {{ $link->name }}
                    </a>
                 @endforeach --}}
            </div>
            
            <div class="col-3 d-flex justify-content-end">
                <div class="dropdown">
                    <button class="btn dropdown-toggle d-flex align-items-center" type="button" >
                        
                        <div class="text-start text-white pe-4">
                            <div class="fw-bold small">{{ Auth::user()->name ?? 'Admin' }}</div>
                            <div class="text-gray small">{{ Auth::user()->email ?? 'email@example.com' }}</div>
                        </div>
                        <img src="{{ asset('images/user.png') }}" alt="Profile" class="img-fluid rounded-circle me-2" style="max-height: 40px;" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <form >
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"  id="logoutButton">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>