# Mini Twitter API

Серверный интерфейс для социальной сети, подобной mini Twitter, созданной на Laravel 12.

### Функции
- Регистрация и авторизация пользователей (token)
- Лента с разбивкой по страницам
- Создание, редактирование и удаление сообщений и ответов
- Лайки (toggle)
- Правила и авторизация
- Автоматическое удаление (постов, ответов и пользователей)

openapi: 3.0.3
info:
  title: Mini Twitter API
  description: REST API built with Laravel for posts, replies and likes.
  version: 1.0.0

servers:
  - url: http://localhost/api
    description: Local development

paths:

  /register:
    post:
      summary: Register new user
      tags: [Auth]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [name, email, password]
              properties:
                name:
                  type: string
                email:
                  type: string
                  format: email
                password:
                  type: string
      responses:
        "201":
          description: User registered

  /login:
    post:
      summary: Login user
      tags: [Auth]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [email, password]
              properties:
                email:
                  type: string
                password:
                  type: string
      responses:
        "200":
          description: Auth token returned

  /feed:
    get:
      summary: Get feed with pagination
      tags: [Posts]
      security:
        - bearerAuth: []
      parameters:
        - in: query
          name: page
          schema:
            type: integer
      responses:
        "200":
          description: Paginated posts list
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/PaginatedPosts'

  /posts:
    post:
      summary: Create post
      tags: [Posts]
      security:
        - bearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [content]
              properties:
                content:
                  type: string
      responses:
        "201":
          description: Post created

  /posts/{id}:
    delete:
      summary: Delete post (soft delete)
      tags: [Posts]
      security:
        - bearerAuth: []
      parameters:
        - in: path
          name: id
          required: true
          schema:
            type: integer
      responses:
        "200":
          description: Post deleted
        "403":
          description: Forbidden

  /posts/{id}/like:
    post:
      summary: Toggle like
      tags: [Likes]
      security:
        - bearerAuth: []
      parameters:
        - in: path
          name: id
          required: true
          schema:
            type: integer
      responses:
        "200":
          description: Like toggled

  /posts/{id}/replies:
    post:
      summary: Create reply
      tags: [Replies]
      security:
        - bearerAuth: []
      parameters:
        - in: path
          name: id
          required: true
          schema:
            type: integer
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [content]
              properties:
                content:
                  type: string
      responses:
        "201":
          description: Reply created

  /replies/{id}:
    delete:
      summary: Delete reply (soft delete)
      tags: [Replies]
      security:
        - bearerAuth: []
      parameters:
        - in: path
          name: id
          required: true
          schema:
            type: integer
      responses:
        "200":
          description: Reply deleted

  /user:
    delete:
      summary: Delete authenticated user account
      tags: [User]
      security:
        - bearerAuth: []
      responses:
        "200":
          description: Account deleted

components:

  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

  schemas:

    Post:
      type: object
      properties:
        id:
          type: integer
        content:
          type: string
        likes_count:
          type: integer
        replies_count:
          type: integer
        liked_by_me:
          type: boolean
        can_delete:
          type: boolean
        created_at:
          type: string
          format: date-time

    PaginatedPosts:
      type: object
      properties:
        data:
          type: array
          items:
            $ref: '#/components/schemas/Post'
        links:
          type: object
        meta:
          type: object
