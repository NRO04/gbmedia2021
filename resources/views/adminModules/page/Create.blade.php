@extends('adminCoreUi.layouts.app')

@section('content')
<body class="c-app">
<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
<div class="c-sidebar-brand d-md-down-none">
<svg class="c-sidebar-brand-full" width="118" height="46" alt="CoreUI Logo">
<use xlink:href="assets/brand/coreui-pro.svg#full"></use>
</svg>
<svg class="c-sidebar-brand-minimized" width="46" height="46" alt="CoreUI Logo">
<use xlink:href="assets/brand/coreui-pro.svg#signet"></use>
</svg>
</div>
<ul class="c-sidebar-nav ps ps--active-y">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link c-active" href="main.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-speedometer"></use>
</svg> Dashboard<span class="badge badge-info">NEW</span></a></li>
<li class="c-sidebar-nav-title">Theme</li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="colors.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-drop1"></use>
</svg> Colors</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="typography.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-pencil"></use>
</svg> Typography</a></li>
<li class="c-sidebar-nav-title">Components</li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-puzzle"></use>
</svg> Base</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/breadcrumb.html"> Breadcrumb</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/cards.html"> Cards</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/carousel.html"> Carousel</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/collapse.html"> Collapse</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/jumbotron.html"> Jumbotron</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/list-group.html"> List group</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/navs.html"> Navs</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/pagination.html"> Pagination</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/popovers.html"> Popovers</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/progress.html"> Progress</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/scrollspy.html"> Scrollspy</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/switches.html"> Switches</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/tabs.html"> Tabs</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="base/tooltips.html"> Tooltips</a></li>
</ul>
</li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-cursor"></use>
</svg> Buttons</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="buttons/buttons.html"> Buttons</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="buttons/brand-buttons.html"> Brand Buttons</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="buttons/button-group.html"> Buttons Group</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="buttons/dropdowns.html"> Dropdowns</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="buttons/loading-buttons.html"> Loading Buttons<span class="badge badge-danger">PRO</span></a></li>
</ul>
</li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="charts.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-chart-pie"></use>
</svg> Charts</a></li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-code"></use>
</svg> Editors</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="editors/code-editor.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-notes"></use>
</svg> Code Editor<span class="badge badge-danger">PRO</span></a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="editors/markdown-editor.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-code"></use>
</svg> Markdown<span class="badge badge-danger">PRO</span></a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="editors/text-editor.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-notes"></use>
</svg> Rich Text Editor<span class="badge badge-danger">PRO</span></a></li>
</ul>
</li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-notes"></use>
</svg> Forms</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="forms/basic-forms.html"> Basic Forms</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="forms/advanced-forms.html"> Advanced<span class="badge badge-danger">PRO</span></a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="forms/validation.html"> Validation<span class="badge badge-danger">PRO</span></a></li>
</ul>
</li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="google-maps.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-map"></use>
</svg> Google Maps<span class="badge badge-danger">PRO</span></a></li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-star"></use>
</svg> Icons</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="icons/coreui-icons-free.html"> CoreUI Icons<span class="badge badge-success">Free</span></a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="icons/coreui-icons-brand.html"> CoreUI Icons - Brand</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="icons/coreui-icons-flag.html"> CoreUI Icons - Flag</a></li>
</ul>
</li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bell"></use>
</svg> Notifications</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="notifications/alerts.html"> Alerts</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="notifications/badge.html"> Badge</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="notifications/modals.html"> Modals</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="notifications/toastr.html"> Toastr<span class="badge badge-danger">PRO</span></a></li>
</ul>
</li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bolt"></use>
</svg> Plugins</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="plugins/calendar.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-calendar"></use>
</svg> Calendar<span class="badge badge-danger">PRO</span></a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="plugins/draggable-cards.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-cursor-move"></use>
</svg> Draggable<span class="badge badge-danger">PRO</span></a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="plugins/spinners.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-sync"></use>
</svg> Spinners<span class="badge badge-danger">PRO</span></a></li>
</ul>
</li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-columns"></use>
</svg> Tables</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="tables/tables.html"> Standard Tables</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="tables/datatables.html"> DataTables<span class="badge badge-danger">PRO</span></a></li>
</ul>
</li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="widgets.html">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-calculator"></use>
</svg> Widgets<span class="badge badge-info">NEW</span></a></li>
<li class="c-sidebar-nav-divider"></li>
<li class="c-sidebar-nav-title">Extras</li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-star"></use>
</svg> Pages</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="login.html" target="_top">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-account-logout"></use>
</svg> Login</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="register.html" target="_top">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-account-logout"></use>
</svg> Register</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="404.html" target="_top">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bug"></use>
</svg> Error 404</a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="500.html" target="_top">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bug"></use>
</svg> Error 500</a></li>
</ul>
</li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-layers"></use>
</svg> Apps</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
</svg> Invoicing</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="apps/invoicing/invoice.html"> Invoice<span class="badge badge-danger">PRO</span></a></li>
</ul>
</li>
<li class="c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
<svg class="c-sidebar-nav-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-envelope-open"></use>
</svg> Email</a>
<ul class="c-sidebar-nav-dropdown-items">
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="apps/email/inbox.html" target="_top"> Inbox<span class="badge badge-danger">PRO</span></a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="apps/email/message.html" target="_top"> Message<span class="badge badge-danger">PRO</span></a></li>
<li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="apps/email/compose.html" target="_top"> Compose<span class="badge badge-danger">PRO</span></a></li>
</ul>
</li>
</ul>
</li>
<li class="c-sidebar-nav-divider"></li>
<li class="c-sidebar-nav-title">Labels</li>
<li class="c-sidebar-nav-item c-d-compact-none c-d-minimized-none"><a class="c-sidebar-nav-label" href="#">
<svg class="c-sidebar-nav-icon text-danger">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bookmark"></use>
</svg> Label danger</a></li>
<li class="c-sidebar-nav-item c-d-compact-none c-d-minimized-none"><a class="c-sidebar-nav-label" href="#">
<svg class="c-sidebar-nav-icon text-info">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bookmark"></use>
</svg> Label info</a></li>
<li class="c-sidebar-nav-item c-d-compact-none c-d-minimized-none"><a class="c-sidebar-nav-label" href="#">
<svg class="c-sidebar-nav-icon text-warning">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bookmark"></use>
</svg> Label warning</a></li>
<li class="c-sidebar-nav-divider"></li>
<li class="c-sidebar-nav-title">System Utilization</li>
<li class="c-sidebar-nav-item px-3 c-d-compact-none c-d-minimized-none">
<div class="text-uppercase mb-1"><small><b>CPU Usage</b></small></div>
<div class="progress progress-xs">
<div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
</div><small class="text-muted">348 Processes. 1/4 Cores.</small>
</li>
<li class="c-sidebar-nav-item px-3 c-d-compact-none c-d-minimized-none">
<div class="text-uppercase mb-1"><small><b>Memory Usage</b></small></div>
<div class="progress progress-xs">
<div class="progress-bar bg-warning" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
</div><small class="text-muted">11444GB/16384MB</small>
</li>
<li class="c-sidebar-nav-item px-3 mb-3 c-d-compact-none c-d-minimized-none">
<div class="text-uppercase mb-1"><small><b>SSD 1 Usage</b></small></div>
<div class="progress progress-xs">
<div class="progress-bar bg-danger" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
</div><small class="text-muted">243GB/256GB</small>
</li>
<div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 0px; height: 817px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 514px;"></div></div></ul>
<button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-unfoldable"></button>
</div>
<div class="c-sidebar c-sidebar-lg c-sidebar-light c-sidebar-right c-sidebar-overlaid" id="aside">
<button class="c-sidebar-close c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-show" responsive="true">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-x"></use>
</svg>
</button>
<ul class="nav nav-tabs nav-underline nav-underline-primary" role="tablist">
<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#timeline" role="tab">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-list"></use>
</svg></a></li>
<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#messages" role="tab">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-speech"></use>
</svg></a></li>
<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#settings" role="tab">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
</svg></a></li>
</ul>

<div class="tab-content">
<div class="tab-pane active" id="timeline" role="tabpanel">
<div class="list-group list-group-accent">
<div class="list-group-item list-group-item-accent-secondary bg-light text-center font-weight-bold text-muted text-uppercase c-small">Today</div>
<div class="list-group-item list-group-item-accent-warning list-group-item-divider">
<div class="c-avatar float-right"><img class="c-avatar-img" src="assets/img/avatars/7.jpg" alt="user@email.com"></div>
<div>Meeting with <strong>Lucas</strong></div><small class="text-muted mr-3">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-calendar"></use>
</svg>&nbsp; 1 - 3pm</small><small class="text-muted">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-location-pin"></use>
</svg>&nbsp; Palo Alto, CA</small>
</div>
<div class="list-group-item list-group-item-accent-info">
<div class="c-avatar float-right"><img class="c-avatar-img" src="assets/img/avatars/4.jpg" alt="user@email.com"></div>
<div>Skype with <strong>Megan</strong></div><small class="text-muted mr-3">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-calendar"></use>
</svg>&nbsp; 4 - 5pm</small><small class="text-muted">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-skype"></use>
</svg>&nbsp; On-line</small>
</div>
<div class="list-group-item list-group-item-accent-secondary bg-light text-center font-weight-bold text-muted text-uppercase c-small">Tomorrow</div>
<div class="list-group-item list-group-item-accent-danger list-group-item-divider">
<div>New UI Project - <strong>deadline</strong></div><small class="text-muted mr-3">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-calendar"></use>
</svg>&nbsp; 10 - 11pm</small><small class="text-muted">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-home"></use>
</svg>&nbsp; creativeLabs HQ</small>
<div class="c-avatars-stack mt-2">
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/2.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/3.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/4.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/5.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/6.jpg" alt="user@email.com"></div>
</div>
</div>
<div class="list-group-item list-group-item-accent-success list-group-item-divider">
<div><strong>#10 Startups.Garden</strong> Meetup</div><small class="text-muted mr-3">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-calendar"></use>
</svg>&nbsp; 1 - 3pm</small><small class="text-muted">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-location-pin"></use>
</svg>&nbsp; Palo Alto, CA</small>
</div>
<div class="list-group-item list-group-item-accent-primary list-group-item-divider">
<div><strong>Team meeting</strong></div><small class="text-muted mr-3">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-calendar"></use>
</svg>&nbsp; 4 - 6pm</small><small class="text-muted">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-home"></use>
</svg>&nbsp; creativeLabs HQ</small>
<div class="c-avatars-stack mt-2">
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/2.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/3.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/4.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/5.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/6.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/7.jpg" alt="user@email.com"></div>
<div class="c-avatar c-avatar-xs"><img class="c-avatar-img" src="assets/img/avatars/8.jpg" alt="user@email.com"></div>
</div>
</div>
</div>
</div>
<div class="tab-pane p-3" id="messages" role="tabpanel">
<div class="message">
<div class="py-3 pb-5 mr-3 float-left">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/7.jpg" alt="user@email.com"><span class="c-avatar-status bg-success"></span></div>
</div>
<div><small class="text-muted">Lukasz Holeczek</small><small class="text-muted float-right mt-1">1:52 PM</small></div>
<div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div><small class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt...</small>
</div>
<hr>
<div class="message">
<div class="py-3 pb-5 mr-3 float-left">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/7.jpg" alt="user@email.com"><span class="c-avatar-status bg-success"></span></div>
</div>
<div><small class="text-muted">Lukasz Holeczek</small><small class="text-muted float-right mt-1">1:52 PM</small></div>
<div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div><small class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt...</small>
</div>
<hr>
<div class="message">
<div class="py-3 pb-5 mr-3 float-left">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/7.jpg" alt="user@email.com"><span class="c-avatar-status bg-success"></span></div>
</div>
<div><small class="text-muted">Lukasz Holeczek</small><small class="text-muted float-right mt-1">1:52 PM</small></div>
<div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div><small class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt...</small>
</div>
<hr>
<div class="message">
<div class="py-3 pb-5 mr-3 float-left">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/7.jpg" alt="user@email.com"><span class="c-avatar-status bg-success"></span></div>
</div>
<div><small class="text-muted">Lukasz Holeczek</small><small class="text-muted float-right mt-1">1:52 PM</small></div>
<div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div><small class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt...</small>
</div>
<hr>
<div class="message">
<div class="py-3 pb-5 mr-3 float-left">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/7.jpg" alt="user@email.com"><span class="c-avatar-status bg-success"></span></div>
</div>
<div><small class="text-muted">Lukasz Holeczek</small><small class="text-muted float-right mt-1">1:52 PM</small></div>
<div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div><small class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt...</small>
</div>
</div>
<div class="tab-pane p-3" id="settings" role="tabpanel">
<h6>Settings</h6>
<div class="c-aside-options">
<div class="clearfix mt-4"><small><b>Option 1</b></small>
<label class="c-switch c-switch-label c-switch-pill c-switch-success c-switch-sm float-right">
<input class="c-switch-input" type="checkbox" checked=""><span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
</label>
</div>
<div><small class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small></div>
</div>
<div class="c-aside-options">
<div class="clearfix mt-3"><small><b>Option 2</b></small>
<label class="c-switch c-switch-label c-switch-pill c-switch-success c-switch-sm float-right">
<input class="c-switch-input" type="checkbox"><span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
</label>
</div>
<div><small class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small></div>
</div>
<div class="c-aside-options">
<div class="clearfix mt-3"><small><b>Option 3</b></small>
<label class="c-switch c-switch-label c-switch-pill c-switch-success c-switch-sm float-right">
<input class="c-switch-input" type="checkbox"><span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
</label>
</div>
</div>
<div class="c-aside-options">
<div class="clearfix mt-3"><small><b>Option 4</b></small>
<label class="c-switch c-switch-label c-switch-pill c-switch-success c-switch-sm float-right">
<input class="c-switch-input" type="checkbox" checked=""><span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
</label>
</div>
</div>
<hr>
<h6>System Utilization</h6>
<div class="text-uppercase mb-1 mt-4"><small><b>CPU Usage</b></small></div>
<div class="progress progress-xs">
<div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
</div><small class="text-muted">348 Processes. 1/4 Cores.</small>
<div class="text-uppercase mb-1 mt-2"><small><b>Memory Usage</b></small></div>
<div class="progress progress-xs">
<div class="progress-bar bg-warning" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
</div><small class="text-muted">11444GB/16384MB</small>
<div class="text-uppercase mb-1 mt-2"><small><b>SSD 1 Usage</b></small></div>
<div class="progress progress-xs">
<div class="progress-bar bg-danger" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
</div><small class="text-muted">243GB/256GB</small>
<div class="text-uppercase mb-1 mt-2"><small><b>SSD 2 Usage</b></small></div>
<div class="progress progress-xs">
<div class="progress-bar bg-success" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
</div><small class="text-muted">25GB/256GB</small>
</div>
</div>
</div>
<div class="c-wrapper">
<header class="c-header c-header-light c-header-fixed">
<button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show">
<svg class="c-icon c-icon-lg">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-menu"></use>
</svg>
</button><a class="c-header-brand d-lg-none c-header-brand-sm-up-center" href="#">
<svg width="118" height="46" alt="CoreUI Logo">
<use xlink:href="assets/brand/coreui-pro.svg#full"></use>
</svg></a>
<button class="c-header-toggler c-class-toggler mfs-3 d-md-down-none" type="button" data-target="#sidebar" data-class="c-sidebar-lg-show" responsive="true">
<svg class="c-icon c-icon-lg">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-menu"></use>
</svg>
</button>
<ul class="c-header-nav d-md-down-none">
<li class="c-header-nav-item px-3"><a class="c-header-nav-link" href="#">Dashboard</a></li>
<li class="c-header-nav-item px-3"><a class="c-header-nav-link" href="#">Users</a></li>
<li class="c-header-nav-item px-3"><a class="c-header-nav-link" href="#">Settings</a></li>
</ul>
<ul class="c-header-nav mfs-auto">
<li class="c-header-nav-item px-3 c-d-legacy-none">
<button class="c-class-toggler c-header-nav-btn" type="button" id="header-tooltip" data-target="body" data-class="c-dark-theme" data-toggle="c-tooltip" data-placement="bottom" title="" data-original-title="Toggle Light/Dark Mode">
<svg class="c-icon c-d-dark-none">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-moon"></use>
</svg>
<svg class="c-icon c-d-default-none">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-sun"></use>
</svg>
</button>
</li>
</ul>
<ul class="c-header-nav">
<li class="c-header-nav-item dropdown d-md-down-none mx-2"><a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bell"></use>
</svg><span class="badge badge-pill badge-danger">5</span></a>
<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
<div class="dropdown-header bg-light"><strong>You have 5 notifications</strong></div><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2 text-success">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user-follow"></use>
</svg> New user registered</a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2 text-danger">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user-unfollow"></use>
</svg> User deleted</a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2 text-info">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-chart"></use>
</svg> Sales report is ready</a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2 text-success">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-basket"></use>
</svg> New client</a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2 text-warning">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-speedometer"></use>
</svg> Server overloaded</a>
<div class="dropdown-header bg-light"><strong>Server</strong></div><a class="dropdown-item d-block" href="#">
<div class="text-uppercase mb-1"><small><b>CPU Usage</b></small></div><span class="progress progress-xs">
<div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
</span><small class="text-muted">348 Processes. 1/4 Cores.</small>
</a><a class="dropdown-item d-block" href="#">
<div class="text-uppercase mb-1"><small><b>Memory Usage</b></small></div><span class="progress progress-xs">
<div class="progress-bar bg-warning" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
</span><small class="text-muted">11444GB/16384MB</small>
</a><a class="dropdown-item d-block" href="#">
<div class="text-uppercase mb-1"><small><b>SSD 1 Usage</b></small></div><span class="progress progress-xs">
<div class="progress-bar bg-danger" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
</span><small class="text-muted">243GB/256GB</small>
</a>
</div>
</li>
<li class="c-header-nav-item dropdown d-md-down-none mx-2"><a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-list-rich"></use>
</svg><span class="badge badge-pill badge-warning">15</span></a>
<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
<div class="dropdown-header bg-light"><strong>You have 5 pending tasks</strong></div><a class="dropdown-item d-block" href="#">
<div class="small mb-1">Upgrade NPM &amp; Bower<span class="float-right"><strong>0%</strong></span></div><span class="progress progress-xs">
<div class="progress-bar bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
</span>
</a><a class="dropdown-item d-block" href="#">
<div class="small mb-1">ReactJS Version<span class="float-right"><strong>25%</strong></span></div><span class="progress progress-xs">
<div class="progress-bar bg-danger" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
</span>
</a><a class="dropdown-item d-block" href="#">
<div class="small mb-1">VueJS Version<span class="float-right"><strong>50%</strong></span></div><span class="progress progress-xs">
<div class="progress-bar bg-warning" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
</span>
</a><a class="dropdown-item d-block" href="#">
<div class="small mb-1">Add new layouts<span class="float-right"><strong>75%</strong></span></div><span class="progress progress-xs">
<div class="progress-bar bg-info" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
</span>
</a><a class="dropdown-item d-block" href="#">
<div class="small mb-1">Angular 8 Version<span class="float-right"><strong>100%</strong></span></div><span class="progress progress-xs">
<div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
</span>
</a><a class="dropdown-item text-center border-top" href="#"><strong>View all tasks</strong></a>
</div>
</li>
<li class="c-header-nav-item dropdown d-md-down-none mx-2"><a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-envelope-open"></use>
</svg><span class="badge badge-pill badge-info">7</span></a>
<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
<div class="dropdown-header bg-light"><strong>You have 4 messages</strong></div><a class="dropdown-item" href="#">
<div class="message">
<div class="py-3 mfe-3 float-left">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/7.jpg" alt="user@email.com"><span class="c-avatar-status bg-success"></span></div>
</div>
<div><small class="text-muted">John Doe</small><small class="text-muted float-right mt-1">Just now</small></div>
<div class="text-truncate font-weight-bold"><span class="text-danger">!</span> Important message</div>
<div class="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt...</div>
</div>
</a><a class="dropdown-item" href="#">
<div class="message">
<div class="py-3 mfe-3 float-left">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/6.jpg" alt="user@email.com"><span class="c-avatar-status bg-warning"></span></div>
</div>
<div><small class="text-muted">John Doe</small><small class="text-muted float-right mt-1">5 minutes ago</small></div>
<div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
<div class="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt...</div>
</div>
</a><a class="dropdown-item" href="#">
<div class="message">
<div class="py-3 mfe-3 float-left">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/5.jpg" alt="user@email.com"><span class="c-avatar-status bg-danger"></span></div>
</div>
<div><small class="text-muted">John Doe</small><small class="text-muted float-right mt-1">1:52 PM</small></div>
<div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
<div class="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt...</div>
</div>
</a><a class="dropdown-item" href="#">
<div class="message">
<div class="py-3 mfe-3 float-left">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/4.jpg" alt="user@email.com"><span class="c-avatar-status bg-info"></span></div>
</div>
<div><small class="text-muted">John Doe</small><small class="text-muted float-right mt-1">4:03 PM</small></div>
<div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
<div class="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt...</div>
</div>
</a><a class="dropdown-item text-center border-top" href="#"><strong>View all messages</strong></a>
</div>
</li>
<li class="c-header-nav-item dropdown"><a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/6.jpg" alt="user@email.com"></div>
</a>
<div class="dropdown-menu dropdown-menu-right pt-0">
<div class="dropdown-header bg-light py-2"><strong>Account</strong></div><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bell"></use>
</svg> Updates<span class="badge badge-info mfs-auto">42</span></a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-envelope-open"></use>
</svg> Messages<span class="badge badge-success mfs-auto">42</span></a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-task"></use>
</svg> Tasks<span class="badge badge-danger mfs-auto">42</span></a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-comment-square"></use>
</svg> Comments<span class="badge badge-warning mfs-auto">42</span></a>
<div class="dropdown-header bg-light py-2"><strong>Settings</strong></div><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
</svg> Profile</a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
</svg> Settings</a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-credit-card"></use>
</svg> Payments<span class="badge badge-secondary mfs-auto">42</span></a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-file"></use>
</svg> Projects<span class="badge badge-primary mfs-auto">42</span></a>
<div class="dropdown-divider"></div><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
</svg> Lock Account</a><a class="dropdown-item" href="#">
<svg class="c-icon mfe-2">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-account-logout"></use>
</svg> Logout</a>
</div>
</li>
<button class="c-header-toggler c-class-toggler mfe-md-3" type="button" data-target="#aside" data-class="c-sidebar-show">
<svg class="c-icon c-icon-lg">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-applications-settings"></use>
</svg>
</button>
</ul>
<div class="c-subheader justify-content-between px-3">

<ol class="breadcrumb border-0 m-0 px-0 px-md-3">
<li class="breadcrumb-item">Home</li>
<li class="breadcrumb-item"><a href="#">Admin</a></li>
<li class="breadcrumb-item active">Dashboard</li>

</ol>
<div class="c-subheader-nav d-md-down-none mfe-2"><a class="c-subheader-nav-link" href="#">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-speech"></use>
</svg></a><a class="c-subheader-nav-link" href="#">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-graph"></use>
</svg> &nbsp;Dashboard</a><a class="c-subheader-nav-link" href="#">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
</svg> &nbsp;Settings</a></div>
</div>
</header>
<div class="c-body">
<main class="c-main">
<div class="container-fluid">
<div id="ui-view"><div><link href="vendors/@coreui/chartjs/css/coreui-chartjs.css" rel="stylesheet">




<div class="fade-in">
<div class="row">
<div class="col-sm-6 col-lg-3">
<div class="card text-white bg-gradient-primary">
<div class="card-body card-body pb-0 d-flex justify-content-between align-items-start">
<div>
<div class="text-value-lg">9.823</div>
<div>Members online</div>
</div>
<div class="btn-group">
<button class="btn btn-transparent dropdown-toggle p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
</svg>
</button>
<div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a></div>
</div>
</div>
<div class="c-chart-wrapper mt-3 mx-3" style="height:70px;"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas class="chart chartjs-render-monitor" id="card-chart1" height="70" style="display: block; width: 271px; height: 70px;" width="271"></canvas>
<div id="card-chart1-tooltip" class="c-chartjs-tooltip top" style="opacity: 0; left: 88.2422px; top: 113.175px;"><div class="c-tooltip-header"><div class="c-tooltip-header-item">January</div></div><div class="c-tooltip-body"><div class="c-tooltip-body-item"><span class="c-tooltip-body-item-color" style="background-color: rgb(50, 31, 219);"></span><span class="c-tooltip-body-item-label">My First dataset</span><span class="c-tooltip-body-item-value">65</span></div></div></div></div>
</div>
</div>

<div class="col-sm-6 col-lg-3">
<div class="card text-white bg-gradient-info">
<div class="card-body card-body pb-0 d-flex justify-content-between align-items-start">
<div>
<div class="text-value-lg">9.823</div>
<div>Members online</div>
</div>
<div class="btn-group">
<button class="btn btn-transparent dropdown-toggle p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
</svg>
</button>
<div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a></div>
</div>
</div>
<div class="c-chart-wrapper mt-3 mx-3" style="height:70px;"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas class="chart chartjs-render-monitor" id="card-chart2" height="70" width="271" style="display: block; width: 271px; height: 70px;"></canvas>
</div>
</div>
</div>

<div class="col-sm-6 col-lg-3">
<div class="card text-white bg-gradient-warning">
<div class="card-body card-body pb-0 d-flex justify-content-between align-items-start">
<div>
<div class="text-value-lg">9.823</div>
<div>Members online</div>
</div>
<div class="btn-group">
<button class="btn btn-transparent dropdown-toggle p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
</svg>
</button>
<div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a></div>
</div>
</div>
<div class="c-chart-wrapper mt-3" style="height:70px;"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas class="chart chartjs-render-monitor" id="card-chart3" height="70" width="303" style="display: block; width: 303px; height: 70px;"></canvas>
</div>
</div>
</div>

<div class="col-sm-6 col-lg-3">
<div class="card text-white bg-gradient-danger">
<div class="card-body card-body pb-0 d-flex justify-content-between align-items-start">
<div>
<div class="text-value-lg">9.823</div>
<div>Members online</div>
</div>
<div class="btn-group">
<button class="btn btn-transparent dropdown-toggle p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
</svg>
</button>
<div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a></div>
</div>
</div>
<div class="c-chart-wrapper mt-3 mx-3" style="height:70px;"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas class="chart chartjs-render-monitor" id="card-chart4" height="70" width="271" style="display: block; width: 271px; height: 70px;"></canvas>
</div>
</div>
</div>

</div>

<div class="card">
<div class="card-body">
<div class="d-flex justify-content-between">
<div>
<h4 class="card-title mb-0">Traffic</h4>
<div class="small text-muted">September 2019</div>
</div>
<div class="btn-toolbar d-none d-md-block" role="toolbar" aria-label="Toolbar with buttons">
<div class="btn-group btn-group-toggle mx-3" data-toggle="buttons">
<label class="btn btn-outline-secondary">
<input id="option1" type="radio" name="options" autocomplete="off"> Day
</label>
<label class="btn btn-outline-secondary active">
<input id="option2" type="radio" name="options" autocomplete="off" checked=""> Month
</label>
<label class="btn btn-outline-secondary">
<input id="option3" type="radio" name="options" autocomplete="off"> Year
</label>
</div>
<button class="btn btn-primary" type="button">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-cloud-download"></use>
</svg>
</button>
</div>
</div>
<div class="c-chart-wrapper" style="height:300px;margin-top:40px;"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas class="chart chartjs-render-monitor" id="main-chart" height="300" width="1260" style="display: block;"></canvas>
<div id="main-chart-tooltip" class="c-chartjs-tooltip center" style="opacity: 0; left: 191.028px; top: 265.784px;"><div class="c-tooltip-header"><div class="c-tooltip-header-item">T</div></div><div class="c-tooltip-body"><div class="c-tooltip-body-item"><span class="c-tooltip-body-item-color" style="background-color: rgba(3, 9, 15, 0.1);"></span><span class="c-tooltip-body-item-label">My First dataset</span><span class="c-tooltip-body-item-value">69</span></div><div class="c-tooltip-body-item"><span class="c-tooltip-body-item-color" style="background-color: rgba(0, 0, 0, 0);"></span><span class="c-tooltip-body-item-label">My Second dataset</span><span class="c-tooltip-body-item-value">100</span></div><div class="c-tooltip-body-item"><span class="c-tooltip-body-item-color" style="background-color: rgba(0, 0, 0, 0);"></span><span class="c-tooltip-body-item-label">My Third dataset</span><span class="c-tooltip-body-item-value">65</span></div></div></div></div>
</div>
<div class="card-footer">
<div class="row text-center">
<div class="col-sm-12 col-md mb-sm-2 mb-0">
<div class="text-muted">Visits</div><strong>29.703 Users (40%)</strong>
<div class="progress progress-xs mt-2">
<div class="progress-bar bg-gradient-success" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
<div class="col-sm-12 col-md mb-sm-2 mb-0">
<div class="text-muted">Unique</div><strong>24.093 Users (20%)</strong>
<div class="progress progress-xs mt-2">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
<div class="col-sm-12 col-md mb-sm-2 mb-0">
<div class="text-muted">Pageviews</div><strong>78.706 Views (60%)</strong>
<div class="progress progress-xs mt-2">
<div class="progress-bar bg-gradient-warning" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
<div class="col-sm-12 col-md mb-sm-2 mb-0">
<div class="text-muted">New Users</div><strong>22.123 Users (80%)</strong>
<div class="progress progress-xs mt-2">
<div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
<div class="col-sm-12 col-md mb-sm-2 mb-0">
<div class="text-muted">Bounce Rate</div><strong>40.15%</strong>
<div class="progress progress-xs mt-2">
<div class="progress-bar" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
</div>
</div>

<div class="row">
<div class="col-sm-6 col-lg-4">
<div class="card">
<div class="card-header bg-facebook content-center">
<svg class="c-icon c-icon-3xl text-white my-4">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-facebook-f"></use>
</svg>
</div>
<div class="card-body row text-center">
<div class="col">
<div class="text-value-xl">89k</div>
<div class="text-uppercase text-muted small">friends</div>
</div>
<div class="c-vr"></div>
<div class="col">
<div class="text-value-xl">459</div>
<div class="text-uppercase text-muted small">feeds</div>
</div>
</div>
</div>
</div>

<div class="col-sm-6 col-lg-4">
<div class="card">
<div class="card-header bg-twitter content-center">
<svg class="c-icon c-icon-3xl text-white my-4">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-twitter"></use>
</svg>
</div>
<div class="card-body row text-center">
<div class="col">
<div class="text-value-xl">973k</div>
<div class="text-uppercase text-muted small">followers</div>
</div>
<div class="c-vr"></div>
<div class="col">
<div class="text-value-xl">1.792</div>
<div class="text-uppercase text-muted small">tweets</div>
</div>
</div>
</div>
</div>

<div class="col-sm-6 col-lg-4">
<div class="card">
<div class="card-header bg-linkedin content-center">
<svg class="c-icon c-icon-3xl text-white my-4">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-linkedin"></use>
</svg>
</div>
<div class="card-body row text-center">
<div class="col">
<div class="text-value-xl">500+</div>
<div class="text-uppercase text-muted small">contacts</div>
</div>
<div class="c-vr"></div>
<div class="col">
<div class="text-value-xl">292</div>
<div class="text-uppercase text-muted small">feeds</div>
</div>
</div>
</div>
</div>

</div>

<div class="row">
<div class="col-md-12">
<div class="card">
<div class="card-header">Traffic &amp; Sales</div>
<div class="card-body">
<div class="row">
<div class="col-sm-6">
<div class="row">
<div class="col-6">
<div class="c-callout c-callout-info"><small class="text-muted">New Clients</small>
<div class="text-value-lg">9,123</div>
</div>
</div>

<div class="col-6">
<div class="c-callout c-callout-danger"><small class="text-muted">Recuring Clients</small>
<div class="text-value-lg">22,643</div>
</div>
</div>

</div>

<hr class="mt-0">
<div class="progress-group mb-4">
<div class="progress-group-prepend"><span class="progress-group-text">Monday</span></div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 34%" aria-valuenow="34" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group mb-4">
<div class="progress-group-prepend"><span class="progress-group-text">Tuesday</span></div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 56%" aria-valuenow="56" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 94%" aria-valuenow="94" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group mb-4">
<div class="progress-group-prepend"><span class="progress-group-text">Wednesday</span></div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 12%" aria-valuenow="12" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 67%" aria-valuenow="67" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group mb-4">
<div class="progress-group-prepend"><span class="progress-group-text">Thursday</span></div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 43%" aria-valuenow="43" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 91%" aria-valuenow="91" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group mb-4">
<div class="progress-group-prepend"><span class="progress-group-text">Friday</span></div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 22%" aria-valuenow="22" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 73%" aria-valuenow="73" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group mb-4">
<div class="progress-group-prepend"><span class="progress-group-text">Saturday</span></div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 53%" aria-valuenow="53" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 82%" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group mb-4">
<div class="progress-group-prepend"><span class="progress-group-text">Sunday</span></div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 9%" aria-valuenow="9" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 69%" aria-valuenow="69" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
</div>

<div class="col-sm-6">
<div class="row">
<div class="col-6">
<div class="c-callout c-callout-warning"><small class="text-muted">Pageviews</small>
<div class="text-value-lg">78,623</div>
</div>
</div>

<div class="col-6">
<div class="c-callout c-callout-success"><small class="text-muted">Organic</small>
<div class="text-value-lg">49,123</div>
</div>
</div>

</div>

<hr class="mt-0">
<div class="progress-group">
<div class="progress-group-header">
<svg class="c-icon progress-group-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
</svg>
<div>Male</div>
<div class="mfs-auto font-weight-bold">43%</div>
</div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-warning" role="progressbar" style="width: 43%" aria-valuenow="43" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group mb-5">
<div class="progress-group-header">
<svg class="c-icon progress-group-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user-female"></use>
</svg>
<div>Female</div>
<div class="mfs-auto font-weight-bold">37%</div>
</div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-warning" role="progressbar" style="width: 43%" aria-valuenow="43" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group">
<div class="progress-group-header align-items-end">
<svg class="c-icon progress-group-icon">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-google"></use>
</svg>
<div>Organic Search</div>
<div class="mfs-auto font-weight-bold mfe-2">191.235</div>
<div class="text-muted small">(56%)</div>
</div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-success" role="progressbar" style="width: 56%" aria-valuenow="56" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group">
<div class="progress-group-header align-items-end">
<svg class="c-icon progress-group-icon">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-facebook-f"></use>
</svg>
<div>Facebook</div>
<div class="mfs-auto font-weight-bold mfe-2">51.223</div>
<div class="text-muted small">(15%)</div>
</div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-success" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group">
<div class="progress-group-header align-items-end">
<svg class="c-icon progress-group-icon">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-twitter"></use>
</svg>
<div>Twitter</div>
<div class="mfs-auto font-weight-bold mfe-2">37.564</div>
<div class="text-muted small">(11%)</div>
</div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-success" role="progressbar" style="width: 11%" aria-valuenow="11" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
<div class="progress-group">
<div class="progress-group-header align-items-end">
<svg class="c-icon progress-group-icon">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-linkedin"></use>
</svg>
<div>LinkedIn</div>
<div class="mfs-auto font-weight-bold mfe-2">27.319</div>
<div class="text-muted small">(8%)</div>
</div>
<div class="progress-group-bars">
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-success" role="progressbar" style="width: 8%" aria-valuenow="8" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
</div>

</div>
<br>
<table class="table table-responsive-sm table-hover table-outline mb-0">
<thead class="thead-light">
<tr>
<th class="text-center">
<svg class="c-icon">
<use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-people"></use>
</svg>
</th>
<th>User</th>
<th class="text-center">Country</th>
<th>Usage</th>
<th class="text-center">Payment Method</th>
<th>Activity</th>
</tr>
</thead>
<tbody>
<tr>
<td class="text-center">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/1.jpg" alt="user@email.com"><span class="c-avatar-status bg-success"></span></div>
</td>
<td>
<div>Yiorgos Avraamu</div>
<div class="small text-muted"><span>New</span> | Registered: Jan 1, 2015</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-us"></use>
</svg>
</td>
<td>
<div class="clearfix">
<div class="float-left"><strong>50%</strong></div>
<div class="float-right"><small class="text-muted">Jun 11, 2015 - Jul 10, 2015</small></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-success" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-cc-mastercard"></use>
</svg>
</td>
<td>
<div class="small text-muted">Last login</div><strong>10 sec ago</strong>
</td>
</tr>
<tr>
<td class="text-center">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/2.jpg" alt="user@email.com"><span class="c-avatar-status bg-danger"></span></div>
</td>
<td>
<div>Avram Tarasios</div>
<div class="small text-muted"><span>Recurring</span> | Registered: Jan 1, 2015</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-br"></use>
</svg>
</td>
<td>
<div class="clearfix">
<div class="float-left"><strong>10%</strong></div>
<div class="float-right"><small class="text-muted">Jun 11, 2015 - Jul 10, 2015</small></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-cc-visa"></use>
</svg>
</td>
<td>
<div class="small text-muted">Last login</div><strong>5 minutes ago</strong>
</td>
</tr>
<tr>
<td class="text-center">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/3.jpg" alt="user@email.com"><span class="c-avatar-status bg-warning"></span></div>
</td>
<td>
<div>Quintin Ed</div>
<div class="small text-muted"><span>New</span> | Registered: Jan 1, 2015</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-in"></use>
</svg>
</td>
<td>
<div class="clearfix">
<div class="float-left"><strong>74%</strong></div>
<div class="float-right"><small class="text-muted">Jun 11, 2015 - Jul 10, 2015</small></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-warning" role="progressbar" style="width: 74%" aria-valuenow="74" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-cc-stripe"></use>
</svg>
</td>
<td>
<div class="small text-muted">Last login</div><strong>1 hour ago</strong>
</td>
</tr>
<tr>
<td class="text-center">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/4.jpg" alt="user@email.com"><span class="c-avatar-status bg-secondary"></span></div>
</td>
<td>
<div>Enéas Kwadwo</div>
<div class="small text-muted"><span>New</span> | Registered: Jan 1, 2015</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-fr"></use>
</svg>
</td>
<td>
<div class="clearfix">
<div class="float-left"><strong>98%</strong></div>
<div class="float-right"><small class="text-muted">Jun 11, 2015 - Jul 10, 2015</small></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 98%" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-cc-paypal"></use>
</svg>
</td>
<td>
<div class="small text-muted">Last login</div><strong>Last month</strong>
</td>
</tr>
<tr>
<td class="text-center">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/5.jpg" alt="user@email.com"><span class="c-avatar-status bg-success"></span></div>
</td>
<td>
<div>Agapetus Tadeáš</div>
<div class="small text-muted"><span>New</span> | Registered: Jan 1, 2015</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-es"></use>
</svg>
</td>
<td>
<div class="clearfix">
<div class="float-left"><strong>22%</strong></div>
<div class="float-right"><small class="text-muted">Jun 11, 2015 - Jul 10, 2015</small></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-info" role="progressbar" style="width: 22%" aria-valuenow="22" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-cc-apple-pay"></use>
</svg>
</td>
<td>
<div class="small text-muted">Last login</div><strong>Last week</strong>
</td>
</tr>
<tr>
<td class="text-center">
<div class="c-avatar"><img class="c-avatar-img" src="assets/img/avatars/6.jpg" alt="user@email.com"><span class="c-avatar-status bg-danger"></span></div>
</td>
<td>
<div>Friderik Dávid</div>
<div class="small text-muted"><span>New</span> | Registered: Jan 1, 2015</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-pl"></use>
</svg>
</td>
<td>
<div class="clearfix">
<div class="float-left"><strong>43%</strong></div>
<div class="float-right"><small class="text-muted">Jun 11, 2015 - Jul 10, 2015</small></div>
</div>
<div class="progress progress-xs">
<div class="progress-bar bg-gradient-success" role="progressbar" style="width: 43%" aria-valuenow="43" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</td>
<td class="text-center">
<svg class="c-icon c-icon-xl">
<use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-cc-amex"></use>
</svg>
</td>
<td>
<div class="small text-muted">Last login</div><strong>Yesterday</strong>
</td>
</tr>
</tbody>
</table>
</div>
</div>
</div>

</div>

</div></div></div>
</div>
</main>
</div>
<footer class="c-footer">
<div><a href="https://coreui.io">CoreUI</a> © 2020 creativeLabs.</div>
<div class="mfs-auto">Powered by&nbsp;<a href="https://coreui.io/pro/">CoreUI Pro</a></div>
</footer>
</div>

<script src="vendors/@coreui/coreui-pro/js/coreui.bundle.min.js"></script>
<!--[if IE]><!-->
<script src="vendors/@coreui/icons/js/svgxuse.min.js"></script>
<!--<![endif]-->
<script>
      new coreui.AsyncLoad(document.getElementById('ui-view'));
      var tooltipEl = document.getElementById('header-tooltip');
      var tootltip = new coreui.Tooltip(tooltipEl);
    </script>

<script type="text/javascript" src="vendors/@coreui/chartjs/js/coreui-chartjs.bundle.js" class="view-script"></script><script type="text/javascript" src="vendors/@coreui/utils/js/coreui-utils.js" class="view-script"></script><script type="text/javascript" src="js/main.js" class="view-script"></script></body>
@endsection