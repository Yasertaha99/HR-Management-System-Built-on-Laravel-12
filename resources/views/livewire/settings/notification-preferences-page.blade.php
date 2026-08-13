<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Notification & Telegram Settings</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Notification Preferences</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    @if($successMessage)
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-lg mb-4" role="alert">
            <i class="fa fa-check-circle mr-2"></i> {{ $successMessage }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Telegram Integration Card -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fa fa-telegram text-info mr-2"></i> Telegram Integration
                    </h5>
                </div>
                <div class="card-body text-center p-4">
                    @if($telegramAccount && $telegramAccount->isVerified())
                        <div class="mb-3">
                            <span class="avatar bg-soft-success text-success rounded-circle p-3 d-inline-block">
                                <i class="fa fa-check-circle" style="font-size: 36px;"></i>
                            </span>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-1">Telegram Connected</h5>
                        <p class="text-muted small mb-3">
                            Chat ID: <code>{{ $telegramAccount->telegram_chat_id }}</code><br>
                            @if($telegramAccount->username) @ {{ $telegramAccount->username }} @endif
                        </p>
                        <button type="button" wire:click="unlinkTelegram" class="btn btn-outline-danger btn-sm rounded-pill px-4">
                            <i class="fa fa-chain-broken mr-1"></i> Unlink Telegram Account
                        </button>
                    @else
                        <div class="mb-3">
                            <span class="avatar bg-soft-warning text-warning rounded-circle p-3 d-inline-block">
                                <i class="fa fa-paper-plane" style="font-size: 36px;"></i>
                            </span>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-1">Link Your Telegram</h5>
                        <p class="text-muted small mb-4">
                            Receive instant real-time alerts and check-in updates directly in Telegram.
                        </p>

                        @if($telegramLinkToken)
                            <div class="alert alert-warning p-3 rounded-lg text-left mb-3">
                                <small class="text-dark font-weight-bold d-block mb-1">Step 1: Open Telegram Bot</small>
                                <small class="text-dark font-weight-bold d-block mb-1">Step 2: Send Command:</small>
                                <code class="d-block bg-white p-2 rounded text-primary font-weight-bold">/start {{ $telegramLinkToken }}</code>
                                <small class="text-muted d-block mt-2"><i class="fa fa-clock-o"></i> Token expires in 15 minutes.</small>
                            </div>
                        @else
                            <button type="button" wire:click="generateTelegramToken" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                                <i class="fa fa-key mr-1"></i> Generate Linking Token
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Preferences Table Card -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fa fa-sliders text-primary mr-2"></i> Event Notification Channels
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover custom-table mb-0 align-middle">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Event Notification</th>
                                    <th class="text-center">In-App (Dashboard)</th>
                                    <th class="text-center">Telegram Channel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notificationTypes as $type)
                                    <tr>
                                        <td>
                                            <strong class="text-dark d-block">{{ $type->label() }}</strong>
                                            <small class="text-muted">Event Code: <code>{{ $type->value }}</code></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="inapp_{{ $type->value }}" 
                                                       wire:click="togglePreference('{{ $type->value }}', 'database')"
                                                       {{ $preferences[$type->value]['database'] ? 'checked' : '' }}>
                                                <label class="custom-control-label cursor-pointer" for="inapp_{{ $type->value }}"></label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="telegram_{{ $type->value }}" 
                                                       wire:click="togglePreference('{{ $type->value }}', 'telegram')"
                                                       {{ $preferences[$type->value]['telegram'] ? 'checked' : '' }}>
                                                <label class="custom-control-label cursor-pointer" for="telegram_{{ $type->value }}"></label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
