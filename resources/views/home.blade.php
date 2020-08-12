@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
     window.addEventListener('DOMContentLoaded', function() {
        const roomId = '{{ $roomId }}';
        const partyId = '{{ $partyId }}';
        const channel = `Party.${partyId}.Room.${roomId}`;
        Echo.private(channel)
        .listen('.party.room.message.created', (event) => {
            console.log(`[party.room.message.created] id: ${event.id}, content: ${event.content}`);
        });

        Echo.channel('App.Announcement')
        .listen('.app.announcement.created', (event) => {
            console.log(`[app.announcement.created] id: ${event.id}, content: ${event.content}`);
        });
     });

</script>
@endsection