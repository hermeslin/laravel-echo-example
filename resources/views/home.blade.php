@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
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
        <div class="col-md-12">
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
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
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
                    <div class="input-group mb-2">
                        <input id="chat-room-message-1" type="text" class="form-control" placeholder="Type Chat Room Message" aria-label="Type Chat Room Message">
                        <select id="chat-message-brocasting-mode" class="custom-select" id="mode">
                            <option selected value="horizon">brocast throuth horizon</option>
                            <option value="directly">brocast directly</option>
                        </select>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="send-chat-room-message-via-api">Send Via Api</button>
                        </div>
                    </div>
                     <div class="input-group">
                        <input id="chat-room-message-2" type="text" class="form-control" placeholder="Type Chat Room Message" aria-label="Type Chat Room Message">
                        <div class="input-group-append">
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
        const Echo = new laravelEcho({
            broadcaster: 'socket.io',
            host: `{{ config('broadcasting.sockets.default.host') }}:{{ config('broadcasting.sockets.default.port') }}`,
            transports: JSON.parse(decodeURIComponent('{{ rawurlencode(json_encode(config('broadcasting.sockets.default.transports'))) }}')),
            client: socketio
        });

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
        const senderName = '{{ $user->name }}';

        const genMsgNode = ({ id, sender_name, message, created_at }) => {
            const senderName = (sender_name)
                ? `${sender_name}: `
                : '';

            const text = (created_at)
                ? `[${created_at}] ${senderName} ${message}`
                : `[${moment().format('YYYY-MM-DD HH:mm:ss')}] ${senderName} ${message}`;

            // Unix Millisecond Timestamp
            const msgId = (id)
                ? `chat-room-message-${id}`
                : `chat-room-message-${moment().format('x')}`;

            const elementP = document.createElement('p');
            const chatroomMessage = document.createTextNode(text);

            elementP.setAttribute('id', msgId);
            elementP.appendChild(chatroomMessage);

            return elementP;
        };

        appendMsg = (target, msgNode) => {
            const chatRoomMessageList = document.querySelector(target);
            chatRoomMessageList.appendChild(msgNode);
            chatRoomMessageList.scrollTop = chatRoomMessageList.scrollHeight;
        };

        Echo.join(`Party.${partyId}.Room.${roomId}`)
        .here((users) => {
            //
            const count = document.createTextNode(`${users.length} user(s)`);
            const existsNodes =  document.querySelector('#chat-room-user-count').childNodes;
            if (existsNodes && existsNodes.length > 0) {
                document.querySelector('#chat-room-user-count').replaceChild(count, existsNodes[0]);
            } else {
                document.querySelector('#chat-room-user-count').appendChild(count);
            }
            console.log(`${users.length} user(s) in this chat room`);
        })
        .joining((user) => {
            const msgNode = genMsgNode({
                message: `${user.name} join this room`
            });

            appendMsg('#chat-room-message-list', msgNode);
            console.log(`${user.name} join this room`);
        })
        .leaving((user) => {
            const msgNode = genMsgNode({
                message: `${user.name}  has left this room`
            });

            appendMsg('#chat-room-message-list', msgNode);
            console.log(`${user.name} has left this room`);
        })
        .listen('.party.room.message.created', (event) => {
            const msgNode = genMsgNode({
                id: event.id,
                sender_name: event.sender_name,
                message: event.content,
                created_at: event.created_at
            });

            appendMsg('#chat-room-message-list', msgNode);
            console.log(`[party.room.message.created] id: ${event.id}, content: ${event.content}`);
        })
        .listenForWhisper('send-message-via-socket', (event) => {
             const msgNode = genMsgNode({
                sender_name: event.sender_name,
                message: event.message
            });

            appendMsg('#chat-room-message-list', msgNode);
            console.log(`send-message-via-socket: ${event.message}`);
        });

        // send chat room message via api contoller
        document.querySelector('#send-chat-room-message-via-api')
        .addEventListener('click', async (event) => {
            const messageEle = document.querySelector('#chat-room-message-1');
            const message = messageEle.value;
            if (!message) {
                return false;
            }

            const modeEle = document.querySelector('#chat-message-brocasting-mode');
            const mode = modeEle.options[modeEle.selectedIndex].value;
            if (!mode) {
                return false;
            }

            try {
                const url = "{{ route('create-party-room-message', ['partyId' => $partyId, 'roomId' => $roomId]) }}";
                const response = await axios.post(url, {
                    mode,
                    message
                });
                console.log({
                    status: response.status,
                    data: response.data.message
                });
                messageEle.value = '';
            } catch (error) {
                 console.log(error);
            }
        });

        document.querySelector('#send-chat-room-message-via-socket')
        .addEventListener('click', async (event) => {
            const messageEle = document.querySelector('#chat-room-message-2');
            const message = messageEle.value;
            if (!message) {
                return false;
            }

            const data = {
                sender_name: senderName,
                message
            };

            Echo.join(`Party.${partyId}.Room.${roomId}`)
           .whisper('send-message-via-socket', data);

            const msgNode = genMsgNode(data);
            appendMsg('#chat-room-message-list', msgNode);

            messageEle.value = '';
        });
     });

</script>
@endsection