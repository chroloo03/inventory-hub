<x-layout title="Staff Accounts">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// admin / staff</div>
            <h1 class="page-title">Staff Accounts</h1>
            <div class="page-subtitle">{{ $staff->total() }} staff member(s) registered</div>
        </div>
        <a href="{{ route('staff.create') }}" class="btn btn-primary">+ Add Staff</a>
    </div>

    @if($staff->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Member Since</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $member)
                    <tr>
                        <td class="td-id">#{{ str_pad($member->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="td-name">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="user-avatar" style="width:30px; height:30px; font-size:11px; flex-shrink:0;">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                {{ $member->name }}
                            </div>
                        </td>
                        <td class="text-mono" style="font-size:12px;">{{ $member->email }}</td>
                        <td>
                            <span class="badge {{ $member->isAdmin() ? 'badge-available' : 'badge-checked_out' }}">
                                {{ ucfirst($member->role) }}
                            </span>
                        </td>
                        <td style="font-size:13px; color:var(--text-dim);">
                            {{ $member->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <div class="td-actions">
                                <a href="{{ route('staff.edit', $member) }}" class="btn btn-ghost btn-sm" title="Edit">✎</a>
                                <button class="btn btn-ghost btn-sm" style="color:var(--red);" title="Delete"
                                    onclick="confirmDelete({{ $member->id }}, '{{ addslashes($member->name) }}', '{{ route('staff.destroy', $member) }}')">
                                    ✕
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>Showing {{ $staff->firstItem() }}–{{ $staff->lastItem() }} of {{ $staff->total() }}</span>
            {{ $staff->links('vendor.pagination.custom') }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-glyph">👥</div>
            <div class="empty-title">No staff accounts yet</div>
            <div class="empty-sub">Add your first staff member to get started.</div>
            <a href="{{ route('staff.create') }}" class="btn btn-primary">+ Add Staff</a>
        </div>
    @endif

</x-layout>
