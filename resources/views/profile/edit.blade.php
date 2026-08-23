@if(Auth::user()->role === 'admin')
  @include('profile.admin-edit')
@else
  @include('profile.student-edit')
@endif