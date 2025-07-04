<?php

namespace App\Livewire\Admin\Panel\Tools;

use App\Models\Page;
use App\Models\Element;
use Livewire\Component;
use App\Models\Template;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use App\Models\PageBuilderSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PageBuilder extends Component
{
    use WithFileUploads;

    public $pages           = [];
    public $templates       = [];
    public $selectedPage    = null;
    public $elements        = [];
    public $newPageTitle    = '';
    public $metaTitle       = '';
    public $metaDescription = '';
    public $isActive        = true;
    public $selectedElement = null;
    public $elementSettings = [
        'color'              => '#000000',
        'background_color'   => '#ffffff',
        'font_size'          => '16px',
        'padding'            => '10px',
        'margin'             => '0px',
        'animation'          => 'none',
        'animation_duration' => '1s',
        'responsive'         => ['desktop' => true, 'tablet' => true, 'mobile' => true],
        'box_shadow'         => 'none',
        'border'             => 'none',
        'opacity'            => '1',
        'grid_columns'       => 1,
                                         // موارد جدید
        'width'              => '100%',  // عرض پیش‌فرض
        'height'             => 'auto',  // ارتفاع پیش‌فرض
        'text_align'         => 'right', // تراز متن پیش‌فرض (چون RTL هست)
        'border_radius'      => '0px',   // شعاع بوردر پیش‌فرض
        'rotation'           => '0deg',  // زاویه چرخش پیش‌فرض
    ];
    public $previewMode     = 'desktop';
    public $newTemplateName = '';
    public $uploadedFile;
    public $content             = '';
    public $animations          = ['none', 'fadeIn', 'slideInLeft', 'slideInRight', 'zoomIn'];
    public $history             = [];
    public $historyIndex        = -1;
    public $copiedElement       = null;
    public $isFullScreenPreview = false;
    public $isAddFormOpen       = false;
    public $isLivePreviewOpen   = false;
    public $autoSaveEnabled     = true;

    protected $rules = [
        'newPageTitle'    => 'required|unique:pages,title',
        'metaTitle'       => 'nullable|string|max:60',
        'metaDescription' => 'nullable|string|max:160',
        'newTemplateName' => 'required|unique:templates,name',
        'uploadedFile'    => 'nullable|file|max:10240',
    ];

    protected $messages = [
        'newPageTitle.required'    => 'لطفاً عنوان صفحه را وارد کنید.',
        'newPageTitle.unique'      => 'این عنوان قبلاً استفاده شده است.',
        'metaTitle.max'            => 'عنوان متا نمی‌تواند بیشتر از ۶۰ کاراکتر باشد.',
        'metaDescription.max'      => 'توضیحات متا نمی‌تواند بیشتر از ۱۶۰ کاراکتر باشد.',
        'newTemplateName.required' => 'لطفاً نام قالب را وارد کنید.',
        'newTemplateName.unique'   => 'این نام قالب قبلاً استفاده شده است.',
        'uploadedFile.max'         => 'فایل نمی‌تواند بزرگ‌تر از ۱۰ مگابایت باشد.',
    ];

    public function mount()
    {
        try {
            $this->pages     = Page::all();
            $this->templates = Template::where('is_public', true)->get();
            $this->elements  = collect();
            $this->loadDefaultSettings();
            $this->startAutoSave();
        } catch (\Exception $e) {
            Log::error('Error in PageBuilder mount: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در بارگذاری داده‌ها رخ داد.', ['type' => 'error']);
        }
    }

    public function loadDefaultSettings()
    {
        $settings = PageBuilderSetting::where('key', 'default_animations')->first();
        if ($settings) {
            $this->animations = array_merge($this->animations, $settings->value);
        }
    }

    public function toggleAddForm()
    {
        $this->isAddFormOpen = ! $this->isAddFormOpen;
    }

    public function toggleLivePreview()
    {
        $this->isLivePreviewOpen = ! $this->isLivePreviewOpen;
    }

    public function createPage()
    {
        $this->validate([
            'newPageTitle'    => 'required|unique:pages,title',
            'metaTitle'       => 'nullable|string|max:60',
            'metaDescription' => 'nullable|string|max:160',
        ]);

        try {
            $page = Page::create([
                'title'            => $this->newPageTitle,
                'slug'             => Str::slug($this->newPageTitle),
                'meta_title'       => $this->metaTitle,
                'meta_description' => $this->metaDescription,
                'is_active'        => $this->isActive,
                'user_id'          => Auth::guard('manager')->user()->id,
            ]);

            $this->pages->push($page); // اضافه کردن صفحه جدید به لیست بدون رفرش کوئری
            $this->resetPageInputs();
            $this->isAddFormOpen = false;
            $this->dispatch('toast', 'صفحه جدید با موفقیت ایجاد شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in createPage: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در ایجاد صفحه رخ داد.', ['type' => 'error']);
        }
    }

    public function togglePageStatus($pageId)
    {
        try {
            $page = Page::findOrFail($pageId);
            $page->update(['is_active' => ! $page->is_active]);

            // به‌روزرسانی فقط صفحه موردنظر در لیست بدون کوئری مجدد کل صفحات
            $this->pages = $this->pages->map(function ($p) use ($page) {
                return $p->id === $page->id ? $page : $p;
            });

            $this->dispatch('toast', 'وضعیت صفحه با موفقیت تغییر کرد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in togglePageStatus: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در تغییر وضعیت صفحه رخ داد.', ['type' => 'error']);
        }
    }

    public function deletePage($pageId)
    {
        try {
            $page = Page::findOrFail($pageId);
            $page->delete();
            $this->pages = Page::all();
            if ($this->selectedPage && $this->selectedPage->id === $pageId) {
                $this->selectedPage    = null;
                $this->elements        = collect();
                $this->metaTitle       = '';
                $this->metaDescription = '';
                $this->isActive        = true;
            }
            $this->dispatch('toast', 'صفحه با موفقیت حذف شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in deletePage: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در حذف صفحه رخ داد.', ['type' => 'error']);
        }
    }

    public function selectPage($pageId)
    {
        try {
            $this->selectedPage    = Page::findOrFail($pageId);
            $this->elements        = Element::where('page_id', $pageId)->orderBy('order')->get();
            $this->metaTitle       = $this->selectedPage->meta_title;
            $this->metaDescription = $this->selectedPage->meta_description;
            $this->isActive        = $this->selectedPage->is_active;
            $this->selectedElement = null;
            $this->history         = [];
            $this->historyIndex    = -1;
            $this->saveToHistory();
        } catch (\Exception $e) {
            Log::error('Error in selectPage: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در انتخاب صفحه رخ داد.', ['type' => 'error']);
        }
    }

    public function updatePage()
    {
        try {
            $this->validate(['metaTitle' => 'nullable|string|max:60', 'metaDescription' => 'nullable|string|max:160']);
            $this->selectedPage->update(['meta_title' => $this->metaTitle, 'meta_description' => $this->metaDescription, 'is_active' => $this->isActive]);
            $this->pages = Page::all();
            $this->dispatch('toast', 'صفحه با موفقیت به‌روزرسانی شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in updatePage: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در به‌روزرسانی صفحه رخ داد.', ['type' => 'error']);
        }
    }

    public function addElement($type)
    {
        if (! $this->selectedPage) {
            $this->dispatch('toast', 'لطفاً ابتدا یک صفحه انتخاب کنید.', ['type' => 'warning']);
            return;
        }
        try {
            $order = Element::where('page_id', $this->selectedPage->id)->max('order') ?? 0;
            $order += 1;
            Element::create([
                'page_id'  => $this->selectedPage->id,
                'type'     => $type,
                'settings' => json_encode($this->elementSettings),
                'content'  => $this->getDefaultContent($type),
                'order'    => $order,
            ]);
            $this->elements = Element::where('page_id', $this->selectedPage->id)->orderBy('order')->get();
            $this->saveToHistory();
        } catch (\Exception $e) {
            Log::error('Error in addElement: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در افزودن المان رخ داد.', ['type' => 'error']);
        }
    }

    public function selectElement($elementId)
    {
        try {
            $this->selectedElement = Element::findOrFail($elementId);
            $this->content         = $this->selectedElement->content;
            $this->elementSettings = json_decode($this->selectedElement->settings, true) ?? $this->elementSettings;
        } catch (\Exception $e) {
            Log::error('Error in selectElement: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در انتخاب المان رخ داد.', ['type' => 'error']);
        }
    }

    public function updateElement()
    {
        if (! $this->selectedElement) {
            return;
        }
        try {
            $this->selectedElement->update(['content' => $this->content, 'settings' => json_encode($this->elementSettings)]);
            $this->elements = Element::where('page_id', $this->selectedPage->id)->orderBy('order')->get();
            $this->saveToHistory();
            $this->dispatch('toast', 'المان با موفقیت به‌روزرسانی شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in updateElement: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در به‌روزرسانی المان رخ داد.', ['type' => 'error']);
        }
    }

    public function deleteElement($elementId)
    {
        try {
            Element::findOrFail($elementId)->delete();
            $this->elements        = Element::where('page_id', $this->selectedPage->id)->orderBy('order')->get();
            $this->selectedElement = null;
            $this->saveToHistory();
            $this->dispatch('toast', 'المان با موفقیت حذف شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in deleteElement: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در حذف المان رخ داد.', ['type' => 'error']);
        }
    }

    public function updateElementOrder($orderedElements)
    {
        try {
            foreach ($orderedElements as $element) {
                Element::where('id', $element['id'])->update(['order' => $element['order']]);
            }
            $this->elements = Element::where('page_id', $this->selectedPage->id)->orderBy('order')->get();
            $this->dispatch('toast', 'ترتیب المان‌ها بروزرسانی شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in updateElementOrder: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در مرتب‌سازی المان‌ها رخ داد.', ['type' => 'error']);
        }
    }

    public function copyElement($elementId)
    {
        try {
            $this->copiedElement = Element::findOrFail($elementId);
            $this->dispatch('toast', 'المان با موفقیت کپی شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in copyElement: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در کپی کردن المان رخ داد.', ['type' => 'error']);
        }
    }

    public function pasteElement()
    {
        if (! $this->copiedElement || ! $this->selectedPage) {
            return;
        }
        try {
            $order = Element::where('page_id', $this->selectedPage->id)->max('order') ?? 0;
            $order += 1;
            Element::create([
                'page_id'  => $this->selectedPage->id,
                'type'     => $this->copiedElement->type,
                'settings' => $this->copiedElement->settings,
                'content'  => $this->copiedElement->content,
                'order'    => $order,
            ]);
            $this->elements = Element::where('page_id', $this->selectedPage->id)->orderBy('order')->get();
            $this->saveToHistory();
            $this->dispatch('toast', 'المان با موفقیت جای‌گذاری شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in pasteElement: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در جای‌گذاری المان رخ داد.', ['type' => 'error']);
        }
    }

    public function uploadFile()
    {
        try {
            $this->validate(['uploadedFile' => 'nullable|file|max:10240']);
            $path          = $this->uploadedFile->store('page_builder_files', 'public');
            $this->content = Storage::url($path);
            $this->updateElement();
        } catch (\Exception $e) {
            Log::error('Error in uploadFile: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در آپلود فایل رخ داد.', ['type' => 'error']);
        }
    }

    public function saveAsTemplate()
    {
        try {
            $this->validate(['newTemplateName' => 'required|unique:templates,name']);
            Template::create(['name' => $this->newTemplateName, 'structure' => json_encode($this->elements->toArray()), 'is_public' => true]);
            $this->templates       = Template::where('is_public', true)->get();
            $this->newTemplateName = '';
            $this->dispatch('toast', 'قالب با موفقیت ذخیره شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in saveAsTemplate: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در ذخیره قالب رخ داد.', ['type' => 'error']);
        }
    }

    public function exportHtml()
    {
        try {
            $html = $this->generateHtml();

            return response()->streamDownload(function () use ($html) {
                echo $html;
            }, $this->selectedPage->slug . '.html');
        } catch (\Exception $e) {
            Log::error('Error in exportHtml: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در خروجی HTML رخ داد.', ['type' => 'error']);
        }
    }

    public function toggleFullScreenPreview()
    {
        $this->isFullScreenPreview = ! $this->isFullScreenPreview;
    }

    public function undo()
    {
        if ($this->historyIndex > 0) {
            $this->historyIndex--;
            $this->restoreFromHistory();
        }
    }

    public function redo()
    {
        if ($this->historyIndex < count($this->history) - 1) {
            $this->historyIndex++;
            $this->restoreFromHistory();
        }
    }

    public function saveToHistory()
    {
        $state = [
            'elements'        => $this->elements->toArray(),
            'selectedElement' => $this->selectedElement ? $this->selectedElement->toArray() : null,
        ];

        // محدود کردن به 50 مورد آخر برای جلوگیری از مصرف بیش از حد حافظه
        $this->history = array_slice($this->history, max(count($this->history) - 49, 0));

        $this->history[]    = $state;
        $this->historyIndex = count($this->history) - 1;
    }

    public function restoreFromHistory()
    {
        $state                 = $this->history[$this->historyIndex];
        $this->elements        = collect($state['elements'])->mapInto(Element::class);
        $this->selectedElement = $state['selectedElement'] ? Element::make($state['selectedElement']) : null;
        if ($this->selectedElement) {
            $this->content         = $this->selectedElement->content;
            $this->elementSettings = json_decode($this->selectedElement->settings, true) ?? $this->elementSettings;
        }
    }

    public function startAutoSave()
    {
        if ($this->autoSaveEnabled) {
            $this->dispatch('autoSave');
        }
    }

    public function autoSave()
    {
        if ($this->selectedPage) {
            $this->updatePage();
            foreach ($this->elements as $element) {
                $element->save();
            }
            $this->dispatch('toast', 'تغییرات به‌صورت خودکار ذخیره شد.', ['type' => 'info']);
        }
    }

    private function getDefaultContent($type)
    {
        return match ($type) {
            'text' => 'متن نمونه',
            'image' => '/storage/default-image.jpeg',
            'button' => 'کلیک کنید',
            'video' => 'https://benobe.ir/uploads/home_video/1666005351_benobe.mp4',
            'form' => '<form><input type="text" placeholder="نام"><input type="email" placeholder="ایمیل"><button type="submit">ارسال</button></form>',
            default => '',
        };
    }

    public function generateHtml()
    {
        $html = '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><title>' . ($this->selectedPage->meta_title ?? 'صفحه بدون عنوان') . '</title><meta name="description" content="' . ($this->selectedPage->meta_description ?? '') . '"><style>';
        $html .= '@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } } @keyframes slideInLeft { from { transform: translateX(-100%); } to { transform: translateX(0); } } @keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } } @keyframes zoomIn { from { transform: scale(0); } to { transform: scale(1); } }';
        $html .= 'body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f9fafb; } .grid { display: grid; gap: 10px; } video { max-width: 100%; } img { max-width: 100%; border-radius: 8px; }';
        $html .= '</style></head><body>';

        foreach ($this->elements as $element) {
            // تنظیمات پیش‌فرض
            $defaultSettings = [
                'color'              => '#000000',
                'background_color'   => '#ffffff',
                'font_size'          => '16px',
                'padding'            => '10px',
                'margin'             => '0px',
                'animation'          => 'none',
                'animation_duration' => '1s',
                'responsive'         => ['desktop' => true, 'tablet' => true, 'mobile' => true],
                'box_shadow'         => 'none',
                'border'             => 'none',
                'opacity'            => '1',
                'grid_columns'       => 1,
                'width'              => '100%',
                'height'             => 'auto',
                'text_align'         => 'right',
                'border_radius'      => '0px',
                'rotation'           => '0deg',
            ];

            // دریافت تنظیمات ذخیره‌شده و ترکیب با پیش‌فرض
            $settings = is_array($element->settings) ? $element->settings : json_decode($element->settings, true);
            $settings = array_merge($defaultSettings, is_array($settings) ? $settings : []);

            // ساخت استایل‌ها
            $style = "color:{$settings['color']};background-color:{$settings['background_color']};font-size:{$settings['font_size']};padding:{$settings['padding']};margin:{$settings['margin']};box-shadow:{$settings['box_shadow']};border:{$settings['border']};opacity:{$settings['opacity']};width:{$settings['width']};height:{$settings['height']};text-align:{$settings['text_align']};border-radius:{$settings['border_radius']};transform:rotate({$settings['rotation']});";
            if ($settings['animation'] !== 'none') {
                $style .= "animation:{$settings['animation']} {$settings['animation_duration']} ease-in-out;";
            }

            // تولید HTML برای المان‌ها
            if ($element->type === 'image') {
                $html .= "<div style='$style' class='grid grid-cols-{$settings['grid_columns']}'><img src='{$element->content}' alt='تصویر'></div>";
            } elseif ($element->type === 'video') {
                $html .= "<div style='$style' class='grid grid-cols-{$settings['grid_columns']}'><video controls><source src='{$element->content}' type='video/mp4'></video></div>";
            } else {
                $html .= "<div style='$style' class='grid grid-cols-{$settings['grid_columns']}'>{$element->content}</div>";
            }
        }

        $html .= '</body></html>';
        return $html;
    }

    public function applyTemplate($templateId)
    {
        // بررسی اینکه صفحه‌ای انتخاب شده است
        if (! $this->selectedPage) {
            $this->dispatch('toast', 'لطفاً ابتدا یک صفحه را انتخاب کنید.', ['type' => 'warning']);
            return;
        }

        try {
            // دریافت قالب از دیتابیس
            $template = Template::findOrFail($templateId);

            // دیکد کردن محتوای قالب به آرایه المان‌ها
            $elementsData = json_decode($template->structure, true);

            // حذف المان‌های فعلی صفحه انتخاب‌شده
            Element::where('page_id', $this->selectedPage->id)->delete();

            // اضافه کردن المان‌های قالب به صفحه
            foreach ($elementsData as $element) {
                Element::create([
                    'page_id'  => $this->selectedPage->id,
                    'type'     => $element['type'],
                    'settings' => json_encode($element['settings']),
                    'content'  => $element['content'],
                    'order'    => $element['order'],
                ]);
            }

            // بروزرسانی لیست المان‌ها در Livewire
            $this->elements = Element::where('page_id', $this->selectedPage->id)->orderBy('order')->get();
            $this->saveToHistory();

            // ارسال پیام موفقیت
            $this->dispatch('toast', 'قالب با موفقیت روی صفحه اعمال شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error in applyTemplate: ' . $e->getMessage());
            $this->dispatch('toast', 'خطایی در اعمال قالب رخ داد.', ['type' => 'error']);
        }
    }

    public function setPreviewMode($mode)
    {
        $this->previewMode = $mode;
    }

    private function resetPageInputs()
    {
        $this->newPageTitle    = '';
        $this->metaTitle       = '';
        $this->metaDescription = '';
        $this->isActive        = true;
    }

    public function render()
    {
        return view('livewire.admin.panel.tools.page-builder', [
            'generatedHtml' => $this->generateHtml(),
        ]);
    }
}
