        <!-- Header -->
        <div class="row header shadows bg-section p-1 mb-2 align-items-center">
            <div class="col-2 d-flex align-items-center">
                <img src="{{ asset('images/qss.png') }}" alt="Logo" class="w-50">
            </div>
            <div class="col-7 d-flex">
                <a href="/dashboard" class="btn cont-btn mx-1 {{ request()->is('dashboard') ? 'selected' : '' }}">
                    Overview
                </a>
                <a href="/missions" class="btn cont-btn mx-1 {{ request()->is('missions*') ? 'selected' : '' }}">
                    Missions
                </a>
                <a href="/locations" class="btn cont-btn mx-1 {{ request()->is('locations*') ? 'selected' : '' }}">
                    Locations
                </a>
                <a href="/pilot" class="btn cont-btn mx-1 {{ request()->is('pilot*') ? 'selected' : '' }}">
                    Pilot
                </a>
                
            </div>
            
            <div class="col-3 d-flex justify-content-end">
                <div class="dropdown">
                    <button class="btn dropdown-toggle d-flex align-items-center" type="button" >
                        
                        <div class="text-start text-white">
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