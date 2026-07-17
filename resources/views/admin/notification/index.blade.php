@extends('layouts.backend.master')

@section('title')
    Peti Pesanan
@endsection

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">
            Peti Pesanan
        </h3>

        <div class="card-toolbar">
            @if($unreadCount > 0)
                <form action="{{ route('notification.readAll') }}"
                      method="POST"
                      onsubmit="return confirm('Adakah anda pasti mahu menandakan semua pesanan sebagai dibaca?')">

                    @csrf

                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-check-double me-1"></i>
                        Tandakan Semua Dibaca

                        <span class="badge badge-circle badge-light ms-2">
                            {{ $unreadCount }}
                        </span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center mb-5">
                <i class="fas fa-check-circle fs-3 me-3"></i>

                <div>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th style="width: 100px;">Status</th>
                        <th>Tajuk</th>
                        <th style="width: 120px;">Modul</th>
                        <th style="width: 190px;">Tarikh</th>
                        <th style="width: 100px;">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($notifications as $notification)
                        <tr class="{{ !$notification->is_read ? 'bg-light-warning' : '' }}">
                            <td>
                                @if(!$notification->is_read)
                                    <span class="badge badge-danger">
                                        Baru
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        Dibaca
                                    </span>
                                @endif
                            </td>

                            <td>
                                <strong>
                                    {{ $notification->title }}
                                </strong>

                                <br>

                                <small class="text-muted">
                                    {{ $notification->message }}
                                </small>
                            </td>

                            <td>
                                {{ $notification->module ?? '-' }}
                            </td>

                            <td>
                                {{ optional($notification->created_at)->format('d-m-Y h:i A') }}
                            </td>

                            <td>
                                <a href="{{ route('notification.read', $notification->id) }}"
                                   class="btn btn-primary btn-sm">

                                    Buka
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <i class="fas fa-inbox fs-2x text-muted mb-3"></i>

                                <div class="text-muted">
                                    Tiada pesanan.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notifications->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</div>

@endsection