{{--
    Notifications Partial
    Renders welcome notification and session flash alerts using pre-computed layout variables.
--}}
@if(!empty($welcomeItems))
    <x-notification-bar type="welcome" :items="$welcomeItems" />
@endif

@if(!empty($flashSuccess))
    <x-notification-bar type="success" :message="$flashSuccess" />
@endif

@if(!empty($flashError))
    <x-notification-bar type="error" :message="$flashError" />
@endif

@if(!empty($flashWarning))
    <x-notification-bar type="warning" :message="$flashWarning" />
@endif

@if(!empty($flashInfo))
    <x-notification-bar type="info" :message="$flashInfo" />
@endif
