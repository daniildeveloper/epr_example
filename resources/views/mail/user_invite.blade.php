@extends('mail.layout')

@section('content')
  <div>
    Вам дан доступ в Integro. Зарегистрируйтесь по <a href="{{ url('/') }}/register/{{ $link->link }}">ссылке</a>:
  </div>

  <div>
    Если ссылка не работает скопируйте и вставьте в браузере: {{ url('/') }}/register/{{ $link->link }}
  </div>
@endsection