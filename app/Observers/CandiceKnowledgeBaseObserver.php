<?php

namespace App\Observers;

use App\Ai\Services\CandiceKnowledgeBase;
use Illuminate\Database\Eloquent\Model;

class CandiceKnowledgeBaseObserver
{
    public function saved(Model $model): void
    {
        CandiceKnowledgeBase::invalidate();
    }

    public function deleted(Model $model): void
    {
        CandiceKnowledgeBase::invalidate();
    }
}
