<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Edit Siswa</h1>
        </div>
    </section>

    <?php foreach($siswa as $ssw) : ?>

    <form action="<?= base_url('siswa/edit_aksi') ?>" method="POST">
        <div class="form-group">
            <label>Nama Siswa</label>
            <input type="hidden" name="id_siswa">
            <input type="text" name="nama_siswa" class="form-control" value="<?= $ssw->nama_siswa ?>">
        </div>
        <div class="form-group">
            <label>Kelas Siswa</label>
            <input type="text" name="kelas_siswa" class="form-control" value="<?= $ssw->kelas_siswa ?>">
        </div>
        <div class="form-group">
            <label>Alamat Siswa</label>
            <textarea name="alamat_siswa" cols="30" rows="10" class="form-control"><?= $ssw->alamat_siswa ?></textarea>
        </div>
        <div class="form-group">
            <label>Nomer Telpon</label>
            <input type="text" name="nomer_telpon" class="form-control" value="<?= $ssw->nomer_telpon ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Simpan</button>
        <button type="reset" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Reset</button>
    </form>

    <?php endforeach ?>

</div>