<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Dashboard</h1>
        </div>
    </section>

    <div class="card mb-3" style="max-width: 540px;">
        <div class="row no-gutters">
            <div class="col-md-4">
                <img alt="image" src="<?= base_url('assets/template') ?>/assets/img/avatar/avatar-1.png" class="rounded-circle mr-1" width="150px" height="150px">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title"><?= $username ?></h5>
                    <p class="card-text"><?= $email ?> | <?= $level ?></p>
                    <p class="card-text"><small class="text-muted">Dibuat : <?= $create_at ?></small></p>
                </div>
            </div>
        </div>
    </div>

</div>