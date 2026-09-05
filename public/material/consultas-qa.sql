-- Consultas somente de leitura. Abra database/database.sqlite.
-- Primeiro observe o esquema; depois adapte os IDs aos seus dados.
PRAGMA table_info(products);
SELECT id, sku, name, price, stock, active FROM products ORDER BY id;
SELECT id, name, email, role, active FROM users ORDER BY id;
SELECT id, number, user_id, status, subtotal, discount, total, payment_status
FROM orders ORDER BY id DESC;
SELECT o.number, i.sku, i.quantity, i.unit_price, i.line_total
FROM orders o JOIN order_items i ON i.order_id = o.id ORDER BY o.id, i.id;
SELECT o.number, e.event, e.description, e.amount, e.created_at
FROM order_events e JOIN orders o ON o.id = e.order_id ORDER BY e.id;
-- Resultado esperado: nenhum pedido com soma de itens divergente.
SELECT o.id, o.subtotal, SUM(i.line_total) AS soma_itens
FROM orders o JOIN order_items i ON i.order_id = o.id
GROUP BY o.id HAVING ROUND(SUM(i.line_total), 2) <> ROUND(o.subtotal, 2);
-- Resultado esperado: nenhum estoque negativo.
SELECT id, sku, stock FROM products WHERE stock < 0;
