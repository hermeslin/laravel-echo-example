@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dashboard</div>
                <div name="exchange-token-block" class="card-body" style="display: block;">
                    Exchange your token first.
                    <form class="form-inline">
                        <input type="text" class="form-control mb-2 mr-sm-2" name="email" required="required" placeholder="Email Address" autocomplete="email" >
                        <input type="password" class="form-control mb-2 mr-sm-2" name="password" required="required" placeholder="Password" autocomplete="current-password">
                        <button type="button" id="exchange-token" class="btn btn-primary mb-2">Exchange token</button>
                    </form>
                </div>

                <div name="token-changed-block" class="card-body" style="display: none;">
                    Your token is:
                    <p id="user-access-token"></p>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div id="announcement-user-socket-id"></div>
                        <div class="d-flex justify-content-between">
                            <span>Announcement</span>
                        </div>
                    </div>

                </div>

                <div class="card-body">
                    <div id="announcement-list" class="overflow-auto" style="height:100px;">
                        <p>[2020-10-10 00:00:00] hi there, welcome to echo server</p>
                    </div>
                </div>

                <div class="card-footer text-muted">
                    <div class="input-group">
                        <input id="announcement-message" type="text" class="form-control" placeholder="Announcement Message" aria-label="Announcement Message">
                        <select id="announcement-message-brocasting-mode" class="custom-select" id="mode">
                            <option selected value="horizon">brocast throuth horizon</option>
                            <option value="directly">brocast directly</option>
                        </select>
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
                <div class="card-header">
                    <div>
                        <div id="chatroom-user-socket-id"></div>
                        <div class="d-flex justify-content-between">
                            <span>Party {{ $partyId }} - Chat Room {{ $roomId }}</span>
                            <span id="chat-room-user-count"></span>
                        </div>
                    </div>
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

        const storeInfo = {
            sender: {},
            roomId: '{{ $roomId }}',
            partyId: '{{ $partyId }}',
            token: null,
            socketConns: [],
        };

        const buildSocketConnAuthHeader = ({ accessToken = null }) => {
            let auth = {};
            if (accessToken) {
                auth = {
                    headers: {
                        Authorization: `Bearer ${accessToken}`
                    }
                };
            }
            return auth;
        };

        // we dont need auth header when socket connect
        // more options see https://socket.io/docs/client-initialization/
        const buildSocketConn = ({
            host = null,
            options = {
                transports: JSON.parse(decodeURIComponent('{{ rawurlencode(json_encode(config('broadcasting.sockets.default.transports'))) }}'))
            }
        }) => {
            const url = (host) ?? '{{ config('broadcasting.sockets.api_auth.host') }}:{{ config('broadcasting.sockets.api_auth.port') }}';
            return socketio(url, options);
        }

        const getSocketConn = ({ name = 'default', host = null, accessToken = null }) => {
            let existsConn = storeInfo.socketConns[name];
            if (!existsConn) {

                storeInfo.socketConns[name] = buildSocketConn({ host, accessToken });
                existsConn = storeInfo.socketConns[name];

                existsConn.on('connect', () => {
                    // show socket id
                    const socketId = `socket.id: ${existsConn.id}`;

                    document.querySelector('#announcement-user-socket-id').textContent = socketId;
                    document.querySelector('#chatroom-user-socket-id').textContent = socketId;
                });
            }
            return existsConn;
        };

        // Exchange access token
        document.querySelector('#exchange-token')
        .addEventListener('click', async (event) => {
            const email = document.querySelector('input[name=email]').value;
            const password = document.querySelector('input[name=password]').value;
            if (!email || !password) {
                return false;
            }

            try {
                // exchange token
                const response = await axios.post("{{ route('oauth-exchange-token') }}", {
                    email,
                    password,
                });
                console.log({
                    status: response.status,
                    data: response.data
                });

                const accessTokenText = document.createTextNode(response.data.access_token);
                storeInfo.token = {
                    access_token: response.data.access_token,
                    token_type: response.data.token_type,
                    expires_in: response.data.expires_in,
                    refresh_token: response.data.refresh_token,
                };

                // get user info
                const userResponse = await axios.get(
                    "{{ route('api-user') }}",
                    {
                        headers: {
                            Authorization: `Bearer ${storeInfo.token.access_token}`
                        }
                    }
                );
                storeInfo.sender = userResponse.data;

                // connet party chat room
                connectPartyChatRoom();

                document.querySelector('#user-access-token').appendChild(accessTokenText)
                document.querySelector('div[name=token-changed-block]')
                .setAttribute("style", 'display: block;');

                document.querySelector('div[name=exchange-token-block]')
                .setAttribute("style", 'display: none;');

            } catch (error) {
                console.log(error);
            }
       });


       // Announcement channel
       ((storeInfo) => {
            const announcementChannel = 'App.Announcement';
            const auth = {};
            const connName = 'singleton';
            const socketIo = getSocketConn({ name: connName });

            socketIo.emit('subscribe', {
                channel: announcementChannel,
                auth,
            });

            socketIo.on('app.announcement.created', (channel, data) => {
                if (channel !== announcementChannel) {
                    console.log('subscribe wrong channel, wtf?');
                    return;
                }

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
                announcementList.appendChild(childP(data));
                announcementList.scrollTop = announcementList.scrollHeight;

                console.log(`[app.announcement.created] id: ${data.id}, content: ${data.content}`);
            });
       })(storeInfo);

        // send announcement via api contoller
        document.querySelector('#send-announcement-message')
        .addEventListener('click', async (event) => {
            if (!storeInfo.token) {
                console.log('exchange your access token first.');
                return false;
            }

            const message = document.querySelector('#announcement-message').value;
            if (!message) {
                return false;
            }

            const modeEle = document.querySelector('#announcement-message-brocasting-mode');
            const mode = modeEle.options[modeEle.selectedIndex].value;
            if (!mode) {
                return false;
            }

            try {
                const response = await axios.post(
                    "{{ route('api-broadcast-announcement') }}",
                    {
                        message,
                        mode
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${storeInfo.token.access_token}`
                        }
                    }
                );
                console.log({
                    status: response.status,
                    data: response.data.message
                });
            } catch (error) {
                 console.log(error);
            }
        });

        // Chat room message, it's a presence channel
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

        const appendMsg = (target, msgNode) => {
            const chatRoomMessageList = document.querySelector(target);
            chatRoomMessageList.appendChild(msgNode);
            chatRoomMessageList.scrollTop = chatRoomMessageList.scrollHeight;
        };

        // send chat room message via api contoller
        document.querySelector('#send-chat-room-message-via-api')
        .addEventListener('click', async (event) => {
            if (!storeInfo.token) {
                console.log('exchange your access token first.');
                return false;
            }

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
                const url  =  "{{ route('api-create-party-room-message', ['partyId' => $partyId, 'roomId' => $roomId]) }}"
                const response = await axios.post(url,
                    {
                        message,
                        mode
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${storeInfo.token.access_token}`
                        }
                    }
                );
                console.log({
                    status: response.status,
                    data: response.data.message
                });

                messageEle.value = '';
            } catch (error) {
                 console.log(error);
            }
        });

        // party chat room cahnnel
        const connectPartyChatRoom = () => {
            const partyRoomChannel = `presence-Party.${storeInfo.partyId}.Room.${storeInfo.roomId}`;
            const auth = buildSocketConnAuthHeader({ accessToken: storeInfo.token.access_token });
            const connName = 'singleton';
            const socketIo = getSocketConn({ name: connName });

            socketIo.emit('subscribe', {
                channel: partyRoomChannel,
                auth,
            });

            socketIo.on('party.room.message.created', (channel, message) => {
                if (channel !== partyRoomChannel) {
                    console.log('subscribe wrong channel, wtf?');
                    return;
                }

                const msgNode = genMsgNode({
                    id: message.id,
                    sender_name: message.sender_name,
                    message: message.content,
                    created_at: message.created_at
                });

                appendMsg('#chat-room-message-list', msgNode);
                console.log(`[party.room.message.created] channel: ${channel}, id: ${message.id}, content: ${message.content}`);
            });

            socketIo.on('client-send-message-via-socket', (channel, data) => {
                const msgNode = genMsgNode({
                    sender_name: data.sender_name,
                    message: data.message
                });

                appendMsg('#chat-room-message-list', msgNode);
                console.log(`send-message-via-socket: ${event.message}`);
            });

            socketIo.on('presence:subscribed', (channel, users) => {
                const count = document.createTextNode(`${users.length} user(s)`);
                const existsNodes =  document.querySelector('#chat-room-user-count').childNodes;
                if (existsNodes && existsNodes.length > 0) {
                    document.querySelector('#chat-room-user-count').replaceChild(count, existsNodes[0]);
                } else {
                    document.querySelector('#chat-room-user-count').appendChild(count);
                }
                console.log(`${users.length} user(s) in this chat room`);
            });

            socketIo.on('presence:joining', (channel, data) => {
                const msgNode = genMsgNode({
                    message: `${data.user_info.name} join this room`
                });

                appendMsg('#chat-room-message-list', msgNode);
                console.log(`${data.user_info.name} join this room`);
            });

            socketIo.on('presence:leaving', (channel, data) => {
                 const msgNode = genMsgNode({
                    message: `${data.user_info.name}  has left this room`
                });

                appendMsg('#chat-room-message-list', msgNode);
                console.log(`${data.user_info.name} has left this room`);
            });
        };

        // send chat room message via socket
        document.querySelector('#send-chat-room-message-via-socket')
        .addEventListener('click', async (event) => {
            if (!storeInfo.token) {
                console.log('exchange your access token first.');
                return false;
            }

            const messageEle = document.querySelector('#chat-room-message-2');

            const message = messageEle.value;
            if (!message) {
                console.log('empty message');
                return false;
            }

            const data = {
                sender_name: storeInfo.sender.name,
                message
            };

            const partyRoomChannel = `presence-Party.${storeInfo.partyId}.Room.${storeInfo.roomId}`;
            const eventName = 'send-message-via-socket';
            const connName = 'singleton';
            const socketIo = getSocketConn({ name: connName });

            // emit the `client event` will only be broadcast to every sockets but the sender.
            // ref https://socket.io/docs/server-api/#Flag-%E2%80%98broadcast%E2%80%99
            // ref https://laravel.com/docs/6.x/broadcasting#client-events
            socketIo.emit('client event', {
                channel: partyRoomChannel,
                event: `client-${eventName}`,
                data
            });

            // append message by ourself
            const msgNode = genMsgNode(data);
            appendMsg('#chat-room-message-list', msgNode);
        });
     });
</script>
@endsection