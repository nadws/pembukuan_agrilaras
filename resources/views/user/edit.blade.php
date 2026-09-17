<input type="hidden" name="id_user" value="{{ $user->id }}">
<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="">Nama</label>
            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label for="">Posisi</label>
            <select name="posisi_id" class="form-control" required>
                <option value="">- Pilih Posisi -</option>
                @foreach ($posisi as $p)
                    <option value="{{ $p->id_posisi }}" @selected((string) $user->posisi_id === (string) $p->id_posisi)>{{ $p->nm_posisi }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label for="">Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label for="">Password <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
            <input type="password" name="password" class="form-control" autocomplete="new-password">
        </div>
    </div>
</div>
