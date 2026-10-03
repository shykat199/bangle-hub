@extends('backend.app')

@push('css')
<style>
  .page-title-box{ padding-bottom:0; }
  .card-section-title{ font-size:1.1rem; font-weight:800; margin-bottom:.75rem; }
  .help-text{ font-size:.85rem; opacity:.85 }
  .sticky-actions{
    position: sticky; bottom: 0; background:#fff; z-index: 50;
    padding: .75rem 1rem; border-top: 1px solid #eee;
    display:flex; gap:.5rem; justify-content:flex-end;
  }
  .preview-box{
    border:1px solid #e5e7eb; border-radius:14px; height:110px;
    display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:16px;
  }
  .code-input{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 13px;
  }
  @media (max-width:575.98px){ .breadcrumb{display:none;} .card{border-radius:12px;} }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-title-box">
      <div class="page-title-right">
        <ol class="breadcrumb m-0">
          <li class="breadcrumb-item"><a href="javascript:void(0)">SIS</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">Front Page</a></li>
          <li class="breadcrumb-item active">Style</li>
        </ol>
      </div>
      <h4 class="page-title">Style (Colors)</h4>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">

        @if(Session::has('msg'))
          <div class="alert alert-success mt-2">
            <strong>{{ Session::get('msg') }}</strong>
          </div>
        @endif

        <form action="{{ route('admin.style.update', $information->id) }}" method="POST" class="mt-3">
          @csrf
          @method('PUT')

          {{-- ================= PRIMARY COLORS ================= --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">Primary Colors</div>

              <div class="row g-3">

                <div class="col-md-3">
                  <label class="fw-semibold">Primary Text Color</label>
                  <input type="color" class="form-control color-input" name="primary_color"
                         value="{{ $information->primary_color ?? '#ffffff' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->primary_color ?? '#ffffff' }}">
                  <small class="help-text d-block mt-1">Button text / primary text</small>
                </div>

                <div class="col-md-3">
                  <label class="fw-semibold">Primary Background</label>
                  <input type="color" class="form-control color-input" name="primary_background"
                         value="{{ $information->primary_background ?? '#5ca3da' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->primary_background ?? '#5ca3da' }}">
                </div>

                <div class="col-md-3">
                  <label class="fw-semibold">Primary Background 2</label>
                  <input type="color" class="form-control color-input" name="primary_background2"
                         value="{{ $information->primary_background2 ?? '#207cca' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->primary_background2 ?? '#207cca' }}">
                  <small class="help-text d-block mt-1">Gradient middle</small>
                </div>

                <div class="col-md-3">
                  <label class="fw-semibold">Primary Background 3</label>
                  <input type="color" class="form-control color-input" name="primary_background3"
                         value="{{ $information->primary_background3 ?? '#1d5fab' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->primary_background3 ?? '#1d5fab' }}">
                  <small class="help-text d-block mt-1">Gradient end</small>
                </div>

                <div class="col-12">
                  <input type="hidden" name="gradient_code" id="gradient_code"
                         value="{{ $information->gradient_code ?? '' }}">

                  <label class="fw-bold text-dark mb-2 d-block">Gradient Preview</label>
                  <div id="gradientPreview" class="preview-box">Gradient Text Preview</div>

                </div>

              </div>
            </div>
          </div>

          {{-- ================= FOOTER THEME (ALL COLORS) ================= --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">Footer Theme (All Colors)</div>

              <div class="row g-3">

                {{-- Footer Background Gradient --}}
                <div class="col-md-4">
                  <label class="fw-semibold">Footer BG Color 1</label>
                  <input type="color" class="form-control color-input" name="footer_bg1"
                         value="{{ $information->footer_bg1 ?? '#0f172a' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_bg1 ?? '#0f172a' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Footer BG Color 2</label>
                  <input type="color" class="form-control color-input" name="footer_bg2"
                         value="{{ $information->footer_bg2 ?? '#020617' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_bg2 ?? '#020617' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Footer BG Color 3</label>
                  <input type="color" class="form-control color-input" name="footer_bg3"
                         value="{{ $information->footer_bg3 ?? '#000000' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_bg3 ?? '#000000' }}">
                </div>

                {{-- Footer Text + Hover + Subtitle --}}
                <div class="col-md-4">
                  <label class="fw-semibold">Footer Text Color</label>
                  <input type="color" class="form-control color-input" name="footer_text"
                         value="{{ $information->footer_text ?? '#e5e7eb' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_text ?? '#e5e7eb' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Footer Link Hover</label>
                  <input type="color" class="form-control color-input" name="footer_link_hover"
                         value="{{ $information->footer_link_hover ?? '#38bdf8' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_link_hover ?? '#38bdf8' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Footer Subtitle Color</label>
                  <input type="color" class="form-control color-input" name="footer_subtitle"
                         value="{{ $information->footer_subtitle ?? '#9ca3af' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_subtitle ?? '#9ca3af' }}">
                </div>

                {{-- Footer Border Gradient --}}
                <div class="col-md-6">
                  <label class="fw-semibold">Footer Border Gradient Start</label>
                  <input type="color" class="form-control color-input" name="footer_border_grad1"
                         value="{{ $information->footer_border_grad1 ?? '#22d3ee' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_border_grad1 ?? '#22d3ee' }}">
                </div>

                <div class="col-md-6">
                  <label class="fw-semibold">Footer Border Gradient End</label>
                  <input type="color" class="form-control color-input" name="footer_border_grad2"
                         value="{{ $information->footer_border_grad2 ?? '#2563eb' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_border_grad2 ?? '#2563eb' }}">
                </div>

                {{-- Pills --}}
                <div class="col-md-3">
                  <label class="fw-semibold">Pill BG</label>
                  <input type="color" class="form-control color-input" name="footer_pill_bg"
                         value="{{ $information->footer_pill_bg ?? '#0f172a' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_pill_bg ?? '#0f172a' }}">
                 
                </div>

                <div class="col-md-3">
                  <label class="fw-semibold">Pill Border</label>
                  <input type="color" class="form-control color-input" name="footer_pill_border"
                         value="{{ $information->footer_pill_border ?? '#94a3b8' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_pill_border ?? '#94a3b8' }}">
                </div>

                <div class="col-md-3">
                  <label class="fw-semibold">Pill Hover BG</label>
                  <input type="color" class="form-control color-input" name="footer_pill_hover_bg"
                         value="{{ $information->footer_pill_hover_bg ?? '#0ea5e9' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_pill_hover_bg ?? '#0ea5e9' }}">
                </div>

                <div class="col-md-3">
                  <label class="fw-semibold">Pill Hover Text</label>
                  <input type="color" class="form-control color-input" name="footer_pill_hover_text"
                         value="{{ $information->footer_pill_hover_text ?? '#0b1120' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_pill_hover_text ?? '#0b1120' }}">
                </div>

                {{-- Underline --}}
                <div class="col-md-4">
                  <label class="fw-semibold">Legal Underline Color</label>
                  <input type="color" class="form-control color-input" name="footer_underline"
                         value="{{ $information->footer_underline ?? '#38bdf8' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_underline ?? '#38bdf8' }}">
                </div>

                {{-- Social --}}
                <div class="col-md-4">
                  <label class="fw-semibold">Social Border</label>
                  <input type="color" class="form-control color-input" name="footer_social_border"
                         value="{{ $information->footer_social_border ?? '#94a3b8' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_social_border ?? '#94a3b8' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Social BG</label>
                  <input type="color" class="form-control color-input" name="footer_social_bg"
                         value="{{ $information->footer_social_bg ?? '#0f172a' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_social_bg ?? '#0f172a' }}">
                </div>

                <div class="col-md-6">
                  <label class="fw-semibold">Social Hover BG</label>
                  <input type="color" class="form-control color-input" name="footer_social_hover_bg"
                         value="{{ $information->footer_social_hover_bg ?? '#0ea5e9' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_social_hover_bg ?? '#0ea5e9' }}">
                </div>

                <div class="col-md-6">
                  <label class="fw-semibold">Social Hover Text</label>
                  <input type="color" class="form-control color-input" name="footer_social_hover_text"
                         value="{{ $information->footer_social_hover_text ?? '#020617' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->footer_social_hover_text ?? '#020617' }}">
                </div>

                {{-- Mobile Nav --}}
                <div class="col-md-4">
                  <label class="fw-semibold">Mobile Nav BG</label>
                  <input type="color" class="form-control color-input" name="mnav_bg"
                         value="{{ $information->mnav_bg ?? '#ffffff' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->mnav_bg ?? '#ffffff' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Mobile Nav Border</label>
                  <input type="color" class="form-control color-input" name="mnav_border"
                         value="{{ $information->mnav_border ?? '#e5e7eb' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->mnav_border ?? '#e5e7eb' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Mobile Nav Icon/Text</label>
                  <input type="color" class="form-control color-input" name="mnav_icon"
                         value="{{ $information->mnav_icon ?? '#1e65b2' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->mnav_icon ?? '#1e65b2' }}">
                </div>

                {{-- Mobile Home Center --}}
                <div class="col-md-4">
                  <label class="fw-semibold">Home Center BG</label>
                  <input type="color" class="form-control color-input" name="mnav_home_bg"
                         value="{{ $information->mnav_home_bg ?? '#00276C' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->mnav_home_bg ?? '#00276C' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Home Center Border</label>
                  <input type="color" class="form-control color-input" name="mnav_home_border"
                         value="{{ $information->mnav_home_border ?? '#ffffff' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->mnav_home_border ?? '#ffffff' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Home Center Icon/Text</label>
                  <input type="color" class="form-control color-input" name="mnav_home_icon"
                         value="{{ $information->mnav_home_icon ?? '#ffffff' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->mnav_home_icon ?? '#ffffff' }}">
                </div>

              </div>
            </div>
          </div>

          {{-- ================= FLOATING BUTTONS & ACCENTS ================= --}}
          <div class="card mb-3">
            <div class="card-body">
              <div class="card-section-title">Floating Buttons & Accents</div>
              <small class="help-text d-block mb-3">
                Back-to-top বাটন আর হোমপেজের "Popular Category" ডট — এগুলো এখন Primary Color থেকে আলাদা, নিজস্ব color।
              </small>

              <div class="row g-3">

                <div class="col-md-4">
                  <label class="fw-semibold">Back-to-Top Background</label>
                  <input type="color" class="form-control color-input" name="floating_btn_bg_color"
                         value="{{ $information->floating_btn_bg_color ?? '#000000' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->floating_btn_bg_color ?? '#000000' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Back-to-Top Icon</label>
                  <input type="color" class="form-control color-input" name="floating_btn_icon_color"
                         value="{{ $information->floating_btn_icon_color ?? '#ffffff' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->floating_btn_icon_color ?? '#ffffff' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Popular Category Dot</label>
                  <input type="color" class="form-control color-input" name="popular_dot_color"
                         value="{{ $information->popular_dot_color ?? '#0d6efd' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->popular_dot_color ?? '#0d6efd' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">WhatsApp Button Background</label>
                  <input type="color" class="form-control color-input" name="whatsapp_btn_bg_color"
                         value="{{ $information->whatsapp_btn_bg_color ?? '#25D366' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->whatsapp_btn_bg_color ?? '#25D366' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">WhatsApp Button Icon</label>
                  <input type="color" class="form-control color-input" name="whatsapp_btn_icon_color"
                         value="{{ $information->whatsapp_btn_icon_color ?? '#ffffff' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->whatsapp_btn_icon_color ?? '#ffffff' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">"View All" Button Background</label>
                  <input type="color" class="form-control color-input" name="view_all_btn_bg_color"
                         value="{{ $information->view_all_btn_bg_color ?? '#0d6efd' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->view_all_btn_bg_color ?? '#0d6efd' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">"View All" Button Text</label>
                  <input type="color" class="form-control color-input" name="view_all_btn_text_color"
                         value="{{ $information->view_all_btn_text_color ?? '#ffffff' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->view_all_btn_text_color ?? '#ffffff' }}">
                </div>

                <div class="col-md-4">
                  <label class="fw-semibold">Category Heading Text</label>
                  <input type="color" class="form-control color-input" name="category_heading_color"
                         value="{{ $information->category_heading_color ?? '#0d6efd' }}">
                  <input type="text" readonly class="form-control mt-1 code-input color-code"
                         value="{{ $information->category_heading_color ?? '#0d6efd' }}">
                  <small class="help-text d-block mt-1">"POPULAR CATEGORY" ar protyek category-r (jemon GADGETS) heading text-er color</small>
                </div>

              </div>
            </div>
          </div>

          <div class="sticky-actions">
            <button type="submit" class="btn btn-success">Update Style</button>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
  function hexToRgba(hex) {
    if (!hex) return 'rgba(0,0,0,1)';
    hex = hex.replace('#','');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    const r = parseInt(hex.substring(0,2),16) || 0;
    const g = parseInt(hex.substring(2,4),16) || 0;
    const b = parseInt(hex.substring(4,6),16) || 0;
    return `rgba(${r}, ${g}, ${b}, 1)`;
  }

  // ✅ nearest code input update (works for any col-md-* width)
  function syncCodes(){
    document.querySelectorAll('input[type="color"].color-input').forEach(colorEl=>{
      const col  = colorEl.closest('.col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12') || colorEl.parentElement;
      const code = col ? col.querySelector('.color-code') : null;
      if (code) code.value = colorEl.value;
    });
  }

  function updateGradient() {
    const tcol = document.querySelector('[name="primary_color"]')?.value || '#ffffff';
    const bg1  = document.querySelector('[name="primary_background"]')?.value || '#5ca3da';
    const bg2  = document.querySelector('[name="primary_background2"]')?.value || '#207cca';
    const bg3  = document.querySelector('[name="primary_background3"]')?.value || '#1d5fab';

    syncCodes();

    const gradient = `linear-gradient(90deg, ${hexToRgba(bg1)} 0%, ${hexToRgba(bg2)} 35%, ${hexToRgba(bg3)} 100%)`;

    const preview = document.getElementById('gradientPreview');
    if (preview) {
      preview.style.background = gradient;
      preview.style.color = tcol;
      preview.textContent = 'Gradient Text Preview';
    }

    const hidden = document.getElementById('gradient_code');
    if (hidden) hidden.value = gradient;
  }

  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.color-input').forEach(el=>{
      el.addEventListener('input', updateGradient);
      el.addEventListener('change', updateGradient);
    });
    updateGradient();
  });
</script>
@endpush
