@extends('layouts.app')

@section('content')
    <h1>DHID login failed</h1>
    <p>Unfortunaly the DHID login has failed. This should normaly never happen but somethimes stuff break. Please contact the support or the site administrator if the error persists!</p>
    
    <hr>
    
    <h4>Reason</h4>
    <p>
    @if(isset($ex)) 
        @if ($ex instanceof \GuzzleHttp\Exception\ConnectException)
        Internal request <strong>{{$ex->getRequest()->getMethod()}}</strong> to "<strong>{{ $ex->getRequest()->getUri() }}</strong>" failed because: <strong>{{ $ex->getMessage() }}</strong>
        @else
        Exception {{ get_class($ex) }} occured!
        @endif
    @elseif(isset($error))
        {{$error}}
    @endif
    </p>

    <hr>

    @if(isset($ex)) 
    <h4>Stack trace</h4>
    <pre>{{ $ex->getTraceAsString() }}</pre>
    @endif
@endsection
