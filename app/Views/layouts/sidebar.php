<?= view('partials/sidebar', [
  'userName' => $userName ?? null,
  'roleName' => $roleName ?? null,
  'permissions' => $permissions ?? null,
]) ?>