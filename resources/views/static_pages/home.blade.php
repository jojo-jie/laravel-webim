@extends('layouts.default')

@section('content')
    <div class="">
        <div>
            <div class="panel-border panel-info">
                <div class="panel-heading">
                    畅聊室 当前在线人数: <span id="count"></span>
                </div>
                <div class="panel-body no-padding">
                    <div class="col-xs-3 user-list">

                    </div>
                    <div class="col-xs-9 no-padding">
                        <div class="chat-list">
                        </div>
                        <div class="message">
                            <div class="text">
                                <textarea></textarea>
                            </div>
                            <div class="send">
                                发送
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/js/jquery.min.js"></script>
    <script src="/vendor/layer/layer.js"></script>
    <script src="/js/webim.js"></script>
@stop
