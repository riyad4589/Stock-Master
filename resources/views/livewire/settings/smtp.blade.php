<div>
    <div class="py-3 px-6 mb-0 bg-gray-200 border-b-1 border-gray-light text-gray-500">
        <h5 class="mb-0">{{ __('Mail Settings') }}</h5>
    </div>
    <div class="p-4">
        <form wire:submit.prevent="update">
            <div class="flex flex-wrap -mx-2 mb-3">
                <div class="w-full md:w-1/3 px-3 mb-4 md:mb-0">
                    <label for="mail_mailer">{{ __('MAIL MAILER') }} <span class="text-red-500">*</span></label>
                    <x-input type="text" wire:model="mail_mailer" name="mail_mailer" required />
                </div>
                <div class="w-full md:w-1/3 px-3 mb-4 md:mb-0">
                    <label for="mail_host">{{ __('MAIL HOST') }} <span class="text-red-500">*</span></label>
                    <x-input type="text" wire:model="mail_host" name="mail_host" required />
                </div>
                <div class="w-full md:w-1/3 px-3 mb-4 md:mb-0">
                    <label for="mail_port">{{ __('MAIL PORT') }} <span class="text-red-500">*</span></label>
                    <x-input type="number" wire:model="mail_port" name="mail_port" required />
                </div>

                <div class="w-full md:w-1/3 px-3 mb-4 md:mb-0">
                    <label for="mail_mailer">{{ __('MAIL MAILER') }}</label>
                    <x-input type="text" wire:model="mail_mailer" name="mail_mailer" />
                </div>
                <div class="w-full md:w-1/3 px-3 mb-4 md:mb-0">
                    <label for="mail_username">{{ __('MAIL USERNAME') }}</label>
                    <x-input type="text" wire:model="mail_username" name="mail_username" />
                </div>
                <div class="w-full md:w-1/3 px-3 mb-4 md:mb-0">
                    <label for="mail_password">{{ __('MAIL PASSWORD') }}</label>
                    <x-input type="password" wire:model="mail_password" name="mail_password" />
                </div>

                <div class="w-full md:w-1/3 px-3 mb-4 md:mb-0">
                    <label for="mail_encryption">{{ __('MAIL ENCRYPTION') }}</label>
                    <x-input type="text" wire:model="mail_encryption" name="mail_encryption" />
                </div>
                <div class="w-full md:w-1/3 px-3 mb-4 md:mb-0">
                    <label for="mail_from_address">{{ __('MAIL FROM ADDRESS') }}</label>
                    <x-input type="email" wire:model="mail_from_address" name="mail_from_address" />
                </div>
                <div class="w-full md:w-1/3 px-3 mb-4 md:mb-0">
                    <label for="mail_from_name">{{ __('MAIL FROM NAME') }} <span class="text-red-500">*</span></label>
                    <x-input type="text" wire:model="mail_from_name" name="mail_from_name" required />
                </div>
            </div>

            <div class="mb-4 md:mb-0">
                <x-button type="submit" primary>
                     {{ __('Save Changes') }}</x-button>
            </div>
        </form>
    </div>
</div>
