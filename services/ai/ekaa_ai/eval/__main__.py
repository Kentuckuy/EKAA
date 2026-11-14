"""CLI Runner for EKAA Evaluation Harness (FR-035, AC-M4-09).
Usage: python -m ekaa_ai.eval --dataset eval/datasets/golden_dataset_v0.json --condition C3
"""
import argparse
import json
from .harness import EvaluationHarness

def main():
    parser = argparse.ArgumentParser(description="EKAA Evaluation Harness")
    parser.add_argument("--dataset", type=str, default="eval/datasets/golden_dataset_v0.json")
    parser.add_argument("--split", type=str, default="dev", choices=["dev", "test"])
    parser.add_argument("--condition", type=str, default="C3", choices=["C0", "C1", "C2", "C3"])
    parser.add_argument("--seed", type=int, default=42)
    args = parser.parse_args()

    try:
        with open(args.dataset, "r", encoding="utf-8") as f:
            data = json.load(f)
    except Exception as e:
        data = {"items": []}

    items = [it for it in data.get("items", []) if it.get("split") == args.split]

    harness = EvaluationHarness()
    results = harness.evaluate_condition(args.condition, items)

    print(json.dumps(results, indent=2))

if __name__ == "__main__":
    main()
