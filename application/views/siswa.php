<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Siswa</h1>
        </div>
    </section>

    <a href="<?= base_url('siswa/tambah') ?>" class="btn btn-primary mb-3"><i class="fas fa-user-plus"></i> Tambah</a>

    <?= $this->session->flashdata('pesan') ?>

    <table class="table table-striped tab-bordered table-hover">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Kelas Siswa</th>
                <th>Alamat Siswa</th>
                <th>Nomer Telpon</th>
                <th>Actions</th>
            </tr>
        </thead>
        <?php $no = 1;
        foreach($siswa as $ssw) : ?>
        <tbody>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $ssw->nama_siswa ?></td>
                <td><?= $ssw->kelas_siswa ?></td>
                <td><?= $ssw->alamat_siswa ?></td>
                <td><?= $ssw->nomer_telpon ?></td>
                <td width="130px">
                    <a href="<?= base_url('siswa/edit/' . $ssw->id_siswa) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                    <a href="<?= base_url('siswa/delete/' . $ssw->id_siswa) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah anda yakin menghapus data ini?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
        </tbody>
        <?php endforeach ?>
    </table>

</div>