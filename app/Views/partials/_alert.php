<?php if(session()->getFlashdata('errors')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Terdapat kesalahan:</strong>
    <ul class="mb-0 mt-1">
        <?php foreach(session()->getFlashdata('errors') as $error): ?>
            <li><?= $error ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
<?php endif; ?>