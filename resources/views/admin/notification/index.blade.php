@extends('layouts.backend.master')

@section('title')
    Peti Pesanan
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Peti Pesanan</h3>
    </div>

    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Tajuk</th>
                    <th>Module</th>
                    <th>Tarikh</th>
                    <th>Tindakan</th>
                </tr>
            </thead>

            <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td>
                            @if(!$notification->is_read)
                                <span class="badge badge-danger">Baru</span>
                            @else
                                <span class="badge badge-success">Dibaca</span>
                            @endif
                        </td>

                        <td>
                            <strong>{{ $notification->title }}</strong><br>
                            <small>{{ $notification->message }}</small>
                        </td>

                        <td>{{ $notification->module ?? '-' }}</td>

                        <td>{{ $notification->created_at->format('d-m-Y h:i A') }}</td>

                        <td>
                            <a href="{{ route('notification.read', $notification->id) }}"
                               class="btn btn-primary btn-sm">
                                Buka
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            Tiada notification
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    </div>
</div>

@endsection