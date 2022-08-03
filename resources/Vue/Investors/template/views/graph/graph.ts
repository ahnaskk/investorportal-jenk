interface GraphValue {
  amount: string | number;
  name: string | number;
  sub_status_id?: string | number;
}
interface ComputedGraph {
  average: string;
  total: string;
  data: ProgressBar[];
}
interface ProgressBar {
  name: string;
  percentage: number;
}
export function drawGraph(data: GraphValue[]): ComputedGraph {
  /**
   * formats the amounts
   */
  let formatter = new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  });
  let total: number = 0;
  let average: number = 0;
  let displayTotal: string = "";
  let displayAverage: string = "";
  let tempData: ProgressBar[] = [];
  if (data) {
    /**
     * Finding sum and average
     */
    total = data.reduce((sum, item) => {
      if (typeof item.amount == "string") {
        sum += Number(item.amount);
      } else {
        sum += item.amount;
      }
      return sum;
    }, 0);
    average = total / data.length;
    if (data.length == 0) average = total;
    /**
     * find percentages of each items
     */
    tempData = data.reduce((array, item) => {
      let obj: ProgressBar = {
        percentage: 0,
        name: "",
      };
      let perc: number;
      let displayPerc: string;
      let key_refactor: string;
      let amount: number | null = null;
      let displayAmount: string | null = null;
      if (typeof item.amount == "string") {
        amount = Number(item.amount);
        displayAmount = amount.toFixed(2) || "0";
      } else {
        displayAmount = item.amount.toFixed(2) || "0";
      }
      /**
       * Show minimum 1% width if the percentage is less than 1.
       */
      let typeConvertedAmount: number | null = null;
      if (typeof item.amount == "string") {
        typeConvertedAmount = Number(item.amount);
      } else {
        typeConvertedAmount = item.amount;
      }
      if (Number(((typeConvertedAmount / total) * 100).toFixed(2)) < 1) {
        perc = 1;
        displayPerc = (perc as any) as string;
      }
      /**
       * Corner case!
       * if total = 0 ; can not perform division
       * should return 0%
       */
      if (total == 0) {
        perc = 0;
        displayPerc = "0";
      } else {
        displayPerc = ((typeConvertedAmount / total) * 100).toFixed(2);
        perc = Number(displayPerc);
      }
      obj.percentage = perc;
      if (typeof item.name == "number") {
        key_refactor = item.name.toFixed(2);
      } else key_refactor = item.name;
      obj["name"] =
        (key_refactor || "Total") +
        ": " +
        formatter.format(typeConvertedAmount) +
        " (" +
        displayPerc +
        "%)";
      array.push(obj);
      return array;
    }, []);

    //truncate
    total = Number(total.toFixed(2));
    average = Number(average.toFixed(2));
    //assign
    displayAverage = formatter.format(average);
    displayTotal = formatter.format(total);
    return {
      total: displayTotal,
      average: displayAverage,
      data: tempData,
    };
  }
}
