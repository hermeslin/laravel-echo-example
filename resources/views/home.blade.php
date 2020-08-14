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

                    Hi {{ $user->name }}, You are logged in!
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Announcement</div>

                    <div class="card-body">
                        <div id="announcement-list" class="overflow-auto" style="height:100px;">
                            <p>[2020-10-10 00:00:00] hi there, welcome to echo server</p>
                        </div>
                    </div>

                    <div class="card-footer text-muted">
                        <div class="input-group">
                            <input id="announcement-message" type="text" class="form-control" placeholder="Announcement Message" aria-label="Announcement Message">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="send-announcement-message">Send</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <span>Party {{ $partyId }} - Chat Room {{ $roomId }}</span>
                    <span id="chat-room-user-count"></span>
                </div>

                    <div class="card-body">
                        <div id="chat-room-message-list" class="overflow-auto" style="height:100px;">
                        </div>
                    </div>

                    <div class="card-footer text-muted">
                        <div class="input-group">
                            <input id="chat-room-message" type="text" class="form-control" placeholder="Type Chat Room Message" aria-label="Type Chat Room Message">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="send-chat-room-message-via-api">Send Via Api</button>
                                <button class="btn btn-outline-secondary" type="button" id="send-chat-room-message-via-socket">Send Via Socket</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
     window.addEventListener('DOMContentLoaded', function() {

        // Announcement
        Echo.channel('App.Announcement')
        .listen('.app.announcement.created', (event) => {

            const childP = (message) => {
                const msgId =`announcement-${message.id}`;
                const msgFormat = `[${message.created_at}] ${message.content}`;
                const announcement = document.createTextNode(msgFormat);

                const elementP = document.createElement('p');
                elementP.setAttribute('id', msgId);
                elementP.appendChild(announcement);
                return elementP;
            };

            const announcementList = document.querySelector('#announcement-list');
            announcementList.appendChild(childP(event));
            announcementList.scrollTop = announcementList.scrollHeight

            console.log(`[app.announcement.created] id: ${event.id}, content: ${event.content}`);
        });

        // send announcement via api contoller
        document.querySelector('#send-announcement-message')
        .addEventListener('click', async (event) => {
            const message = document.querySelector('#announcement-message').value;
            if (!message) {
                return false;
            }

            try {
                const response = await axios.post("{{ route('broadcast-announcement') }}", {
                    message
                });
                console.log({
                    status: response.status,
                    data: response.data.message
                });
            } catch (error) {
                 console.log(error);
            }
        });

        // Chat room message, it's a presence channel
        const roomId = '{{ $roomId }}';
        const partyId = '{{ $partyId }}';
        Echo.join(`Party.${partyId}.Room.${roomId}`)
        .here((users) => {
            //
            const count = document.createTextNode(`${users.length} user(s)`);
            document.querySelector('#chat-room-user-count').appendChild(count);
            console.log(`${users.length} user(s) in this chat room`);
        })
        .joining((user) => {
            console.log(`${users.name} join this room`);
        })
        .leaving((user) => {
            console.log(`${users.name} join has left this room`);
        })
        .listen('.party.room.message.created', (event) => {

            const childP = (message) => {
                const msgId =`chat-room-message-${message.id}`;
                const msgFormat = `[${message.created_at}] ${message.sender_name}: ${message.content}`;
                const chatroomMessage = document.createTextNode(msgFormat);

                const elementP = document.createElement('p');
                elementP.setAttribute('id', msgId);
                elementP.appendChild(chatroomMessage);
                return elementP;
            };

            const chatRoomMessageList = document.querySelector('#chat-room-message-list');
            chatRoomMessageList.appendChild(childP(event));
            chatRoomMessageList.scrollTop = chatRoomMessageList.scrollHeight

            console.log(`[party.room.message.created] id: ${event.id}, content: ${event.content}`);
        });

        // send chat room message via api contoller
        document.querySelector('#send-chat-room-message-via-api')
        .addEventListener('click', async (event) => {
            const message = document.querySelector('#chat-room-message').value;
            if (!message) {
                return false;
            }

            try {
                const response = await axios.post("{{ route('create-party-room-message', ['partyId' => $partyId, 'roomId' => $roomId]) }}", {
                    message
                });
                console.log({
                    status: response.status,
                    data: response.data.message
                });
            } catch (error) {
                 console.log(error);
            }
        });
     });

</script>
@endsection