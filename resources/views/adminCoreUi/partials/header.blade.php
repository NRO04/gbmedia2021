<header class="c-header c-header-light c-header-fixed">
    <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show">
        <svg class="c-icon c-icon-lg">
            <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-menu')}}"></use>
        </svg>
    </button>
    <a class="c-header-brand d-lg-none c-header-brand-sm-up-center" href="#">
        <svg width="118" height="46" alt="CoreUI Logo">
            <use xlink:href="{{global_asset('assets/brand/coreui-pro.svg#full')}}"></use>
        </svg>
    </a>
    </a>
    <button class="c-header-toggler c-class-toggler mfs-3 d-md-down-none" type="button" data-target="#sidebar" data-class="c-sidebar-lg-show" responsive="true">
        <svg class="c-icon c-icon-lg">
            <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-menu')}}"></use>
        </svg>
    </button>
    <ul class="c-header-nav d-md-down-none">
        <li class="c-header-nav-item px-3">
            <a class="c-header-nav-link" href="{{global_asset('dashboard')}}">
                <i class="fa fa-home"></i>
            </a>
        </li>
    </ul>
    @if(Auth::check())
        <ul class="c-header-nav mfs-auto">
            <chatnotification :user="{{ Auth::user()->id }}"></chatnotification>
        </ul>
    @endif
    <ul class="c-header-nav mfs-auto">
        <li class="@if (Auth::check() && Auth::user()->is_admin != 1) d-none @endif">
            <small class="badge badge-success p-2 text-dark">NAVEGANDO COMO ADMIN</small>
        </li>
        @if(isset($assignments) && count($assignments) > 0)
            <li class="c-header-nav-item px-3 c-d-legacy-none">
                <select name="" id="change-studio" class="form-control form-control-sm">
                    @foreach($assignments AS $studio)
                        <option @if (tenant('id') == $studio->toTenant->id) selected @endif value="{{ $studio->toTenant->id }}">{{ $studio->toTenant->studio_name }}</option>
                    @endforeach
                </select>
            </li>
        @endif
        <li class="c-header-nav-item px-3 c-d-legacy-none d-none">
            <a class="c-subheader-nav-link" href="{{ route('chat.index')}}">
                <svg class="c-icon">
                    <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-speech')}}"></use>
                </svg>
            </a>
        </li>
    </ul>
    <ul class="c-header-nav">
        <li class="c-header-nav-item dropdown d-md-down-none mx-2 d-none">
            <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <svg class="c-icon">
                    <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-bell')}}"></use>
                </svg>
                <span class="badge badge-pill badge-danger">5</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
                <div class="dropdown-header bg-light"><strong>You have 5 notifications</strong></div>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2 text-success">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-user-follow')}}"></use>
                    </svg>
                    New user registered
                </a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2 text-danger">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-user-unfollow')}}"></use>
                    </svg>
                    User deleted
                </a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2 text-info">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-chart')}}"></use>
                    </svg>
                    Sales report is ready
                </a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2 text-success">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-basket')}}"></use>
                    </svg>
                    New client
                </a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2 text-warning">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-speedometer')}}"></use>
                    </svg>
                    Server overloaded
                </a>
                <div class="dropdown-header bg-light"><strong>Server</strong></div>
                <a class="dropdown-item d-block" href="#">
                    <div class="text-uppercase mb-1"><small><b>CPU Usage</b></small></div>
                    <span class="progress progress-xs">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </span>
                    <small class="text-muted">348 Processes. 1/4 Cores.</small>
                </a>
                <a class="dropdown-item d-block" href="#">
                    <div class="text-uppercase mb-1"><small><b>Memory Usage</b></small></div>
                    <span class="progress progress-xs">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                    </span>
                    <small class="text-muted">11444GB/16384MB</small>
                </a>
                <a class="dropdown-item d-block" href="#">
                    <div class="text-uppercase mb-1"><small><b>SSD 1 Usage</b></small></div>
                    <span class="progress progress-xs">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                    </span>
                    <small class="text-muted">243GB/256GB</small>
                </a>
            </div>
        </li>

        <li class="c-header-nav-item dropdown d-md-down-none mx-2 d-none">
            <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <svg class="c-icon">
                    <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-list-rich')}}"></use>
                </svg>
                <span class="badge badge-pill badge-warning">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
                <div class="dropdown-header bg-light"><strong>You have 5 pending tasks</strong></div>
                <a class="dropdown-item d-block" href="#">
                    <div class="small mb-1">Upgrade NPM &amp; Bower<span class="float-right"><strong>0%</strong></span></div>
                    <span class="progress progress-xs">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </span>
                </a>
                <a class="dropdown-item d-block" href="#">
                    <div class="small mb-1">ReactJS Version<span class="float-right"><strong>25%</strong></span></div>
                    <span class="progress progress-xs">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </span>
                </a>
                <a class="dropdown-item d-block" href="#">
                    <div class="small mb-1">VueJS Version<span class="float-right"><strong>50%</strong></span></div>
                    <span class="progress progress-xs">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </span>
                </a>
                <a class="dropdown-item d-block" href="#">
                    <div class="small mb-1">Add new layouts<span class="float-right"><strong>75%</strong></span></div>
                    <span class="progress progress-xs">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </span>
                </a>
                <a class="dropdown-item d-block" href="#">
                    <div class="small mb-1">Angular 8 Version<span class="float-right"><strong>100%</strong></span></div>
                    <span class="progress progress-xs">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </span>
                </a>
                <a class="dropdown-item text-center border-top" href="#"><strong>View all tasks</strong></a>
            </div>
        </li>

        @if(Auth::check())
            <notification :user="{{ Auth::user()->id }}"></notification>

            <li class="c-header-nav-item dropdown mx-2">
            <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <div class="c-avatar">
                    <img class="c-avatar-img" src="{{ global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . Auth::user()->avatar) }}" alt="{{ Auth::user()->first_name . " " . Auth::user()->last_name }}">
                    @if(Cache::has('is_online' . auth()->user()->id))
                        <span class="c-avatar-status bg-success"></span>
                    @else
                        <span class="c-avatar-status bg-danger"></span>
                    @endif
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right pt-0">
                <div class="dropdown-header bg-light py-2 text-center">
                    <div class="gb-c-avatar-inner">
                        <img src="{{ global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . Auth::user()->avatar) }}" alt="{{ Auth::user()->first_name . " " . Auth::user()->last_name }}" class="c-avatar-img">
                    </div>
                    <div class="user-info mt-2">
                        <h5>{{ Auth::user()->first_name . " " . Auth::user()->last_name }}</h5>
                        <div class="user-role">
                            <span class="text-muted">{{ Auth::user()->roles->pluck('name')->implode(', ') }}</span>
                        </div>
                        <div class="user-member-since">
                            <small class="text-muted">Miembro desde: {{ Carbon\Carbon::parse(Auth::user()->admission_date)->format('d/M/Y') }}<b></b></small>
                        </div>
                        <div class="user_online">
                            @if(Cache::has('is_online' . auth()->user()->id))
                                <span class="text-success">Online</span>
                            @else
                                <span class="text-danger">Offline</span>
                            @endif
                        </div>
                    </div>
                </div>
                {{--<a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-bell')}}"></use>
                    </svg>
                    Updates<span class="badge badge-info mfs-auto">42</span>
                </a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-envelope-open')}}"></use>
                    </svg>
                    Messages<span class="badge badge-success mfs-auto">42</span>
                </a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-task')}}"></use>
                    </svg>
                    Tasks<span class="badge badge-danger mfs-auto">42</span>
                </a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-comment-square')}}"></use>
                    </svg>
                    Comments<span class="badge badge-warning mfs-auto">42</span>
                </a>
                <div class="dropdown-header bg-light py-2"><strong>Settings</strong></div>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-user')}}"></use>
                    </svg>
                    Profile
                </a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-settings')}}"></use>
                    </svg>
                    Settings</a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-credit-card')}}"></use>
                    </svg>
                    Payments<span class="badge badge-secondary mfs-auto">42</span>
                </a>
                <a class="dropdown-item" href="#">
                    <svg class="c-icon mfe-2">
                        <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-file')}}"></use>
                    </svg>
                    Projects<span class="badge badge-primary mfs-auto">42</span>
                </a>
                <div class="dropdown-divider"></div>--}}
                <a class="dropdown-item d-none" href="#" onclick='ChangeTheme()'>
                    <button class="c-class-toggler c-header-nav-btn pl-0" type="button" id="header-tooltip" data-target="body" data-class="c-dark-theme" data-toggle="c-tooltip" data-placement="bottom" title="Toggle Light/Dark Mode" onclick='ChangeTheme()'>
                        <svg class="c-icon">
                            <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-settings')}}"></use>
                        </svg>
                    </button>
                    Perfil
                </a>
                <a class="dropdown-item d-none" href="#" onclick='ChangeTheme()'>
                    <button class="c-class-toggler c-header-nav-btn pl-0" type="button" id="header-tooltip" data-target="body" data-class="c-dark-theme" data-toggle="c-tooltip" data-placement="bottom" title="Toggle Light/Dark Mode" onclick='ChangeTheme()'>
                        <svg class="c-icon c-d-dark-none">
                            <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-moon')}}"></use>
                        </svg>
                        <svg class="c-icon c-d-default-none">
                            <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-sun')}}"></use>
                        </svg>
                        <span class="px-2">Cambiar tema</span>
                    </button>
                </a>
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <button class="c-class-toggler c-header-nav-btn pl-0" type="button" id="header-tooltip" data-target="body" data-class="c-dark-theme" data-toggle="c-tooltip" data-placement="bottom" title="Toggle Light/Dark Mode" onclick='ChangeTheme()'>
                        <svg class="c-icon">
                            <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-account-logout')}}"></use>
                        </svg>
                    </button>
                    Cerrar sesión
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </li>
        @endif
        <button class="c-header-toggler c-class-toggler mfe-md-3 d-none" type="button" data-target="#aside" data-class="c-sidebar-show">
            <svg class="c-icon c-icon-lg">
                <use xlink:href="{{global_asset('vendors/@coreui/icons/svg/free.svg#cil-applications-settings')}}"></use>
            </svg>
        </button>
    </ul>

    @push('scripts')
        <script>
            $('#change-studio').on('change', function () {
                let tenant_id = $(this).val();
                console.log(tenant_id)
                window.location.replace("/studio/generate-impersonate-token/" + tenant_id);
                // window.open("/studio/generate-impersonate-token/" + tenant_id);
            });
        </script>
    @endpush
</header>
