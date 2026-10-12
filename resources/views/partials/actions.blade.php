<div class="d-flex gap-1 justify-content-end">
    <a href="{{ $edit }}" class="btn btn-sm btn-outline-secondary" data-testid="edit-{{ $tid }}"><i class="bi bi-pencil"></i></a>
    <form method="POST" action="{{ $delete }}" onsubmit="return confirm('Yakin hapus data ini?')">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-outline-danger" data-testid="delete-{{ $tid }}"><i class="bi bi-trash"></i></button>
    </form>
</div>
