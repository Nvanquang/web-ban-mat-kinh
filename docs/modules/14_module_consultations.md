# Module: Consultations (Frontend)

## Objective
- Cho phép customer gửi câu hỏi tư vấn đến admin
- Xem danh sách câu hỏi đã gửi và trạng thái phản hồi
- Hiển thị phản hồi từ admin (khi `status = resolved`)
- Customer chỉ thấy consultations của chính mình

---

## Wireframe thiết kế giao diện

### Consultation Page — `/consultations`
```
┌─────────────────────────────────────────────────────────────────┐
│  [NAVBAR]                                                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Consultation & Support                                         │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  Ask a Question                                         │   │
│  │─────────────────────────────────────────────────────────│   │
│  │  Your Question *                                        │   │
│  │  ┌─────────────────────────────────────────────────┐   │   │
│  │  │                                                 │   │   │
│  │  │  Type your question about glasses, prescriptions│   │   │
│  │  │  sizing, or anything else...                    │   │   │
│  │  │                                                 │   │   │
│  │  └─────────────────────────────────────────────────┘   │   │
│  │  0 / 2000 characters                                    │   │
│  │                           [  Submit Question  ]         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  My Questions (3)                                               │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  ● Jan 15  [resolved]                                   │   │
│  │  "What's the difference between TR90 and titanium?"     │   │
│  │  ─────────────────────────────────────────────────────  │   │
│  │  💬 Admin Reply:                                         │   │
│  │  TR90 is a nylon-based material that is more flexible   │   │
│  │  and lightweight, while titanium...                     │   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │  ● Jan 12  [pending]                                    │   │
│  │  "Do you offer prescription lens replacement?"          │   │
│  │  ─────────────────────────────────────────────────────  │   │
│  │  ⏳ Awaiting response from our team...                   │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

**Style notes:**
- Send form: card `bg #ffffff`, shadow `--shadow-sm`, border-radius `--border-radius-md`
- Textarea: min-height `120px`, resize vertical only
- Submit button: `bg #0ea5e9`, float right
- `resolved` badge: `bg #dcfce7`, text `#16a34a`
- `pending` badge: `bg #fef3c7`, text `#d97706`
- Admin reply block: `bg #f0f9ff` (sky-50), border-left `4px solid #0ea5e9`, padding `1rem`
- Pending state: `color --text-muted`, italic
- Question item: card style, `border --border-color`, `border-radius --border-radius-sm`

---

## Routes

| Method | URL | Handler | Auth |
|---|---|---|---|
| GET | `/consultations` | `ConsultationController::index` | Login required |
| POST | `/consultations/send` | `ConsultationController::send` | Login required |

---

## UI Pages

### `views/consultations/index.php`
- Flash message
- Form gửi câu hỏi: textarea `content`, character counter (JS), Submit button
- Danh sách consultations của customer:
  - `sent_at` (formatted date)
  - `status` badge (pending / resolved)
  - `content` (câu hỏi)
  - Nếu `status = resolved`: hiển thị admin `reply` trong reply block
  - Nếu `status = pending`: hiển thị "Awaiting response..."
- Empty state nếu chưa có câu hỏi nào

---

## Data Processing Flow

### GET `/consultations`
```
1. requireAuth()
2. $customerId = Session::getUser()['id']
3. ConsultationModel::getByCustomer($customerId)
   → ORDER BY sent_at DESC
4. render('consultations/index', [consultations, title])
```

### POST `/consultations/send`
```
1. requireAuth()
2. $content = trim($_POST['content'] ?? '')
3. Validate:
   - empty($content) → flash 'Question cannot be empty' + redirect
   - mb_strlen($content) > 2000 → flash 'Question too long (max 2000 chars)' + redirect
4. ConsultationModel::create([
       'customer_id' => currentUser['id'],
       'content'     => $content,
   ])
   - false → flash 'Failed to send, please try again' + redirect
5. flash success 'Your question has been sent! We will reply shortly.'
6. redirect('/consultations')
```

---

## Validation

| Field | Rule | Error |
|---|---|---|
| `content` | Required | "Question cannot be empty." |
| `content` | Min 10 ký tự | "Question is too short (min 10 characters)." |
| `content` | Max 2000 ký tự | "Question is too long (max 2000 characters)." |

---

## Database Interaction

**Bảng:** `consultations`

| Action | Method | SQL notes |
|---|---|---|
| Lấy consultations của customer | `getByCustomer(customerId)` | `WHERE customer_id = ? ORDER BY sent_at DESC` |
| Tạo consultation mới | `create(array $data)` | `INSERT INTO consultations (customer_id, content)` |

> `reply` và `status` **không** được customer set — chỉ admin cập nhật.  
> `sent_at` auto set bởi MySQL `DEFAULT CURRENT_TIMESTAMP`.

---

## Permissions

| Route | Yêu cầu |
|---|---|
| GET `/consultations` | Login required |
| POST `/consultations/send` | Login required |
| Xem reply của người khác | Không thể — query luôn filter theo `customer_id` |

---

## Error Handling

| Tình huống | Xử lý |
|---|---|
| Validate fail | flash error + redirect `/consultations` |
| DB insert fail | flash error + redirect `/consultations` |
| Chưa login | redirect `/auth/login` |

---

## Redirect Rules

| Sự kiện | Redirect | Old input |
|---|---|---|
| Send thành công | `/consultations` | Không |
| Send thất bại (validate) | `/consultations` | Không (textarea reset) |
| Send thất bại (DB) | `/consultations` | Không |

> Không cần preserve old input vì textarea nằm cùng trang với danh sách.

---

## Done ✅

- [ ] GET `/consultations` render form + danh sách consultations của user
- [ ] POST `/consultations/send` validate, insert DB, flash, redirect
- [ ] Hiển thị admin reply khi `status = resolved`
- [ ] Hiển thị "Awaiting response" khi `status = pending`
- [ ] Character counter JS (0/2000) trên textarea
- [ ] Empty state khi chưa có câu hỏi nào
- [ ] Login required guard hoạt động
- [ ] Customer chỉ thấy consultations của chính mình
