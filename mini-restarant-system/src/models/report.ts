export interface Report {
    period: { from: string; to: string };
    total_orders: number;
    total_revenue: string;
    pending_orders: number;
    completed_orders: number;
    cancelled_orders: number;
    average_order_value: string;
    orders_by_type: { dine_in: number; takeaway: number };
    daily_revenue: Record<string, number>;
    top_foods: { food: { name: string; image: string }; total_quantity: number; total_revenue: number }[];
    payment_methods: { payment_method: string; count: number; total: number }[];
}
