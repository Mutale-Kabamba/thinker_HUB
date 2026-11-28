@if(session()->has('impersonating_parent_id'))
    @php
        $impersonatingParent = \App\Models\User::find(session('impersonating_parent_id'));
    @endphp
    @if($impersonatingParent)
    <div style="background: linear-gradient(135deg, #0f766e 0%, #064e3b 100%); color: #ffffff; padding: 10px 18px; display: flex; align-items: center; justify-content: space-between; font-size: 13px; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 99999; border-bottom: 2px solid rgba(255, 255, 255, 0.2);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #34d399; box-shadow: 0 0 8px #34d399;"></span>
            <span>
                Parent View: You are currently viewing <strong>{{ auth()->user()->name }}</strong>'s child workspace (Code: <code style="background: rgba(0,0,0,0.25); padding: 2px 6px; border-radius: 4px; font-family: monospace;">{{ auth()->user()->unique_code ?? 'N/A' }}</code>)
            </span>
        </div>
        <form method="POST" action="{{ route('parent.switch-back') }}" style="margin: 0; display: inline;">
            @csrf
            <button type="submit" style="background-color: #ffffff; color: #064e3b; padding: 5px 14px; border-radius: 8px; font-weight: 700; font-size: 12px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); transition: background-color 0.15s ease;">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Return to Parent Portal</span>
            </button>
        </form>
    </div>
    @endif
@endif
