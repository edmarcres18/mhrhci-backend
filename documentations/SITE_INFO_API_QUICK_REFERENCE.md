# Site Information API - Quick Reference

## 📡 Base URL
```
/api/v1/contacts
```

## 🎯 Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/email` | Email address |
| GET | `/tel` | Telephone number |
| GET | `/phone` | Phone number |
| GET | `/telegram` | Telegram handle + URL |
| GET | `/facebook` | Facebook handle + URL |
| GET | `/viber` | Viber number + deep link |
| GET | `/whatsapp` | WhatsApp number + wa.me link |
| GET | `/all` | All contacts (bulk) |

## 🔒 Security

- **Rate Limit:** 120 requests/minute
- **Cache:** 1 hour (3600s)
- **Error Logging:** Yes
- **Auto Cache Clear:** On data updates

## 📊 Response Format

### Success (200)
```json
{
  "success": true,
  "data": {
    "email_address": "contact@example.com",
    "type": "email"
  }
}
```

### Error (404)
```json
{
  "success": false,
  "message": "Email address not configured",
  "data": null
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "An error occurred while fetching email address"
}
```

## 💻 Quick Usage

### JavaScript
```javascript
const res = await fetch('/api/v1/contacts/all');
const data = await res.json();
console.log(data.data);
```

### cURL
```bash
curl -X GET http://localhost/api/v1/contacts/email
```

## ✅ Status
**PRODUCTION READY** - Secured, cached, and monitored!
