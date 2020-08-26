@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
    <hr>
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

        const storeInfo = {
            token: null,
            token: null,
            announcementSocket: null,
            chatRoomSocket: null,
        };

        const getSocketIo = ({ host = null, accessToken = null } = {}) => {
            const url = (host) ?? window.location.hostname + ':6001';
            const options = {};

            if (accessToken) {
                options.auth = {
                    headers: {
                        Authorization: `Bearer ${accessToken}`
                    }
                };
            }

            return io(url, options);
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

                document.querySelector('#user-access-token').appendChild(accessTokenText)
                document.querySelector('div[name=token-changed-block]')
                .setAttribute("style", 'display: block;');

                if (!storeInfo.chatRoomSocket) {
                    storeInfo.chatRoomSocket = getSocketIo({
                        accessToken: response.data.access_token
                    })
                }

                document.querySelector('div[name=exchange-token-block]')
                .setAttribute("style", 'display: none;');

            } catch (error) {
                console.log(error);
            }
       });


       // Announcement
       ((storeInfo) => {
           let socketIo = null;
           if (!storeInfo.announcementSocket) {
                storeInfo.announcementSocket = getSocketIo();
                socketIo = storeInfo.announcementSocket;
            } else {
                socketIo = storeInfo.announcementSocket;
            }

            socketIo.emit('subscribe', {
                channel: 'App.Announcement',
                auth: {},
            });

            socketIo.on('app.announcement.created', (channel, data) => {
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

            try {
                const response = await axios.post(
                    "{{ route('api-broadcast-announcement') }}",
                    {
                        message
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

        document.querySelector('#send-chat-room-message-via-api')
        .addEventListener('click', async (event) => {
            if (!storeInfo.token) {
                console.log('exchange your access token first.');
                return false;
            }
            console.log(`get access token: ${storeInfo.token.access_token}`);
        });
     });
</script>
@endsection