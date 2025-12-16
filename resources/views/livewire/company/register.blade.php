<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')]
class extends Component {
    //
}; ?>

<div>
    <div class="flex flex-col gap-6">
        <x-auth-header title="カンパニー登録" description="地域の事業者として登録します" />
        <div class="text-center text-zinc-600 dark:text-zinc-400">
            <p>カンパニー登録画面は準備中です。</p>
        </div>
    </div>
</div>
