# Job Board with Advanced Filtering

## **Project Setup**
### **1. Clone the Repository**
```sh
git clone https://github.com/ramizmurtaza/astudio_assignment
cd astudio_assignment
```

### **2. Install Dependencies**
```sh
composer install
npm install
```

### **3. Configure Environment**
Copy the `.env.example` file and update database settings:
```sh
cp .env.example .env
```
Edit `.env` and configure your database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=job_board
DB_USERNAME=root
DB_PASSWORD=
```

### **4. Run Migrations & Seed Data**
```sh
php artisan migrate --seed
```
This will create tables and insert dummy data.

### **5. Start Development Server**
```sh
php artisan serve
```
The application will be available at `http://127.0.0.1:8000`.

---

## **API Documentation**
### **1. List Jobs**
#### **GET /api/jobs**
Retrieve job listings with optional filtering.

#### **Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `filter` | string | Complex filter query |

#### **Example Request:**
```sh
curl "http://127.0.0.1:8000/api/jobs"
```

#### **Response Example:**
```json
{
    "status": true,
    "message": "Request successful.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 14,
                "title": "Instrument Sales Representative",
                "description": "Esse rerum dolore nulla molestiae qui occaecati officia. Neque maiores dolorem veritatis maxime in cupiditate dolorem. Rerum tempora et quia earum.",
                "company_name": "Leffler Ltd",
                "salary_min": "65959.00",
                "salary_max": "97673.00",
                "is_remote": false,
                "job_type": "full-time",
                "status": "draft",
                "published_at": "2025-01-04T04:19:20.000000Z",
                "created_at": "2025-03-16T19:09:47.000000Z",
                "updated_at": "2025-03-16T19:09:47.000000Z",
                "languages": [
                    {
                        "id": 1,
                        "name": "PHP"
                    }
                ],
                "locations": [
                    {
                        "id": 1,
                        "city": "New York"
                    }
                ],
                "categories": [
                    {
                        "id": 2,
                        "name": "Data Science"
                    }
                ],
                "job_attributes": [
                    {
                        "id": 53,
                        "job_id": 14,
                        "attribute_id": 1,
                        "value": "9",
                        "created_at": "2025-03-16T19:09:47.000000Z",
                        "updated_at": "2025-03-16T19:09:47.000000Z",
                        "attribute": {
                            "id": 1,
                            "name": "years_experience",
                            "type": "number",
                            "options": null,
                            "created_at": null,
                            "updated_at": null
                        }
                    }
                ]
            }
        ],
        "first_page_url": "http:\/\/127.0.0.1:8000\/api\/jobs?page=1",
        "from": 1,
        "last_page": 23,
        "last_page_url": "http:\/\/127.0.0.1:8000\/api\/jobs?page=23",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http:\/\/127.0.0.1:8000\/api\/jobs?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": "http:\/\/127.0.0.1:8000\/api\/jobs?page=2",
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "next_page_url": "http:\/\/127.0.0.1:8000\/api\/jobs?page=2",
        "path": "http:\/\/127.0.0.1:8000\/api\/jobs",
        "per_page": 1,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }
}
```

---

## **Filtering Syntax**
### **Basic Filtering**
- **Exact Match:** `(job_type=full-time)`
- **Greater/Less Than:** `(salary_min>=60000)`
- **Boolean Fields:** `(is_remote=true)`

### **Filtering by Relationships**
- **Languages (HAS_ANY):** `(languages HAS_ANY (PHP,JavaScript))`
- **Locations (IS_ANY):** `(locations IS_ANY (New York,Remote))`
- **Categories:** `(categories IS_ANY (Software Development,Marketing))`

### **Filtering by Dynamic Attributes (EAV)**
- **Years of Experience:** `(attribute:years_experience>=3)`
- **Degree Requirement:** `(attribute:degree_required=true)`

### **Logical Operators**
- **AND Condition:** `(job_type=full-time AND salary_min>=60000)`
- **OR Condition:** `(is_remote=true OR locations IS_ANY (Remote,London))`
- **Grouped Conditions:** `(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript)))`

### **Complex Query Example**
#### **Filter: Full-time jobs requiring PHP or JavaScript, in New York or Remote, with 3+ years of experience**
```sh
curl "http://127.0.0.1:8000/api/jobs?filter=(job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3&pagination=1&per_page=1&page=1"
```

#### **Filter: Full-time remote jobs requiring PHP or JavaScript, in New York, with 5+ years of experience**
```sh
curl "http://127.0.0.1:8000/api/jobs?filter=(job_type=full-time AND is_remote=true AND (languages HAS_ANY (PHP,JavaScript))) AND (locations IS_ANY (New York)) AND attributes:years_experience>=5)&pagination=1&per_page=10&page=1"
```

### **Additional Query Examples**
#### **Filter: Find remote jobs where the minimum salary is at least $60,000 and the maximum salary does not exceed $120,000**
```sh
curl "http://127.0.0.1:8000/api/jobs?filter=(is_remote=true AND salary_min>=60000 AND salary_max<=120000&pagination=1&per_page=20&page=1"
```

#### **Filter: Find remote jobs in the DevOps category where PHP or Laravel is one of the required languages**
```sh
curl "http://127.0.0.1:8000/api/jobs?filter=(categories IS_ANY (DevOps) AND (is_remote=true) AND (languages HAS_ANY (PHP,Laravel)) )&pagination=1&per_page=10&page=1"
```

## **Assumptions & Design Decisions**
- **EAV Model:** `Allows flexibility for different job types.`
- **Filtering Syntax:** `Designed to be expressive and readable.`
- **ndexing Strategy:** `Indexes added to improve performance.`
