@extends('layouts.admin-app')

@section('css')
    @vite('resources/css/admin/admin-detail.css')
@endsection

@section('content')
    <div class="detail__content">
        <div class="detail__header">
            <h1 class="content__header--item">勤怠詳細</h1>
        </div>
        @if ($errors->any())
    <div style="color: red; margin: 20px 0;">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
        <form class="form" action="{{ url('/admin/attendance/' . $attendance->id) }}" method="post">
            @csrf
                <div class="form__content">
                    <div class="form__group">
                        <label class="form__header" for="name">名前</label>
                        <div class="form__input-group">
                            <input class="form__input form__input--name" id="name" type="text" name="name" value="{{ $user->name }}"
                                readonly>
                        </div>
                    </div>
                    <div class="form__group">
                        <label class="form__header">日付</label>
                        <div class="form__input-group">
                            {{-- 日付は表示のみ（修正不可）。名前欄と同様に枠線（border）を外す --}}
                            <input class="form__input form__input--date" type="text" value="{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}" readonly>
                            <input class="form__input form__input--date" type="text" name="new_date" value="{{ \Carbon\Carbon::parse($attendance->date)->format('m月d日') }}" readonly>
                        </div>
                    </div>

                    <div class="form__group">
                        <label class="form__header" for="new_clock_in">出勤・退勤</label>
                        <div class="form__input-group">
                            <input class="form__input" id="new_clock_in" type="text" name="new_clock_in"
                                value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
                            <p>〜</p>
                            <input class="form__input" type="text" name="new_clock_out"
                                value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                        </div>
                    </div>

                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error('new_clock_in')
                                {{ $message }}
                            @enderror
                            @error('new_clock_out')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- 休憩は「休憩」「休憩1」「休憩2」…とセクションを分けて表示 --}}
                    @php
                        $breaks = $attendance->breakTimes;
                    @endphp
                    @foreach($breaks as $index => $break)
                        <div class="form__group">
                            <label class="form__header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</label>
                            <div class="form__input-group">
                                <input class="form__input" type="text" name="new_break_in[{{ $index }}]"
                                    value="{{ $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '' }}">
                                <p>〜</p>
                                <input class="form__input" type="text" name="new_break_out[{{ $index }}]"
                                    value="{{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '' }}">
                            </div>
                        </div>
                        <div class="error-message">
                            <div></div>
                            <div class="error-message__item">
                                @error('new_break_in.' . $index)<p>{{ $message }}</p>@enderror
                                @error('new_break_out.' . $index)<p>{{ $message }}</p>@enderror
                            </div>
                        </div>
                    @endforeach
                    {{-- 新しい休憩を追加できるよう末尾に空欄を1つ用意 --}}
                    @php $newBreakIndex = count($breaks); @endphp
                    <div class="form__group">
                        <label class="form__header">{{ $newBreakIndex === 0 ? '休憩' : '休憩' . ($newBreakIndex + 1) }}</label>
                        <div class="form__input-group">
                            <input class="form__input" type="text" name="new_break_in[{{ $newBreakIndex }}]" value="">
                            <p>〜</p>
                            <input class="form__input" type="text" name="new_break_out[{{ $newBreakIndex }}]" value="">
                        </div>
                    </div>
                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error('new_break_in.' . $newBreakIndex)<p>{{ $message }}</p>@enderror
                            @error('new_break_out.' . $newBreakIndex)<p>{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="form__group">
                        <label class="form__header" for="comment">備考</label>
                        <div class="form__input-group">
                            <textarea class="form__textarea" name="comment" id="comment">{{ $attendance->comment }}</textarea>
                        </div>
                    </div>
                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error ('comment')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form__button">
                    <button class="form__button--submit" type="submit">修正</button>
                </div>
        </form>
    </div>
@endsection
