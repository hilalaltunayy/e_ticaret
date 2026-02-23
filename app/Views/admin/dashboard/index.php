<?php
/** @var \App\DTO\Admin\DashboardDTO $dto */
?>
<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="mb-0">Analytics & Finance Dashboard</h2>
    <span class="text-muted small">Able Pro demo blend</span>
  </div>

  <div class="row g-3 mb-4">
    <?php foreach (($dto->orderCards ?? []) as $card): ?>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small"><?= esc($card->title ?? '-') ?></div>
            <div class="fs-3 fw-semibold"><?= esc($card->value ?? 0) ?></div>
            <div class="small text-muted"><?= esc($card->subtitle ?? ' ') ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <strong>Overview (Analytics)</strong>
          <div class="btn-group btn-group-sm" role="group" aria-label="overview-actions">
            <button class="btn btn-outline-primary">Day</button>
            <button class="btn btn-outline-primary">Week</button>
            <button class="btn btn-primary">Month</button>
          </div>
        </div>
        <div class="card-body">
          <div id="overview-chart-1"></div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Category Split (Finance)</strong></div>
        <div class="card-body">
          <div id="category-donut-chart"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>New Orders</strong></div>
        <div class="card-body">
          <div id="new-orders-graph"></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>New Users</strong></div>
        <div class="card-body">
          <div id="new-users-graph"></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Overview Snapshot A</strong></div>
        <div class="card-body">
          <div id="overview-chart-2"></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Overview Snapshot B</strong></div>
        <div class="card-body">
          <div id="overview-chart-3"></div>
          <div class="mt-3" id="overview-chart-4"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Cashflow (Finance)</strong></div>
        <div class="card-body">
          <div id="cashflow-bar-chart"></div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Calendar Template</strong></div>
        <div class="card-body">
          <div id="pc-datepicker-6"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Top 10 Authors</strong></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr><th>#</th><th>Author</th><th class="text-end">Sales</th></tr>
              </thead>
              <tbody>
                <?php $rank = 1; foreach (array_slice(($dto->topAuthors ?? []), 0, 10) as $a): ?>
                  <tr>
                    <td><?= esc($rank++) ?></td>
                    <td><?= esc($a['label'] ?? '-') ?></td>
                    <td class="text-end"><?= esc($a['value'] ?? 0) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Top 10 Digital Books</strong></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr><th>#</th><th>Book</th><th class="text-end">Sales</th></tr>
              </thead>
              <tbody>
                <?php $rank = 1; foreach (array_slice(($dto->topDigitalBooks ?? []), 0, 10) as $b): ?>
                  <tr>
                    <td><?= esc($rank++) ?></td>
                    <td><?= esc($b['label'] ?? '-') ?></td>
                    <td class="text-end"><?= esc($b['value'] ?? 0) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Latest Orders (Analytics block)</strong></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Customer</th>
                  <th>Status / Product</th>
                  <th class="text-end">Amount</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
              <?php if (!empty($dto->latestOrders)): ?>
                <?php foreach ($dto->latestOrders as $o): ?>
                  <tr>
                    <td><?= esc($o->id ?? '-') ?></td>
                    <td><?= esc($o->customerName ?? '-') ?></td>
                    <td><?= esc($o->status ?? '-') ?></td>
                    <td class="text-end"><?= esc(number_format((float)($o->totalAmount ?? 0), 2)) ?></td>
                    <td><?= esc($o->createdAt ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><strong>Component Buttons</strong></div>
        <div class="card-body d-flex flex-wrap gap-2">
          <button class="btn btn-primary">Primary</button>
          <button class="btn btn-outline-primary">Outline</button>
          <button class="btn btn-secondary">Secondary</button>
          <button class="btn btn-success">Success</button>
          <button class="btn btn-warning">Warning</button>
          <button class="btn btn-danger">Danger</button>
          <button class="btn btn-light border">Light</button>
          <button class="btn btn-dark">Dark</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('assets/admin/js/plugins/datepicker-full.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/new-orders-graph.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/new-users-graph.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/overview-chart.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/cashflow-bar-chart.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/category-donut-chart.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/widgets/widget-calender.js') ?>"></script>
<?= $this->endSection() ?>